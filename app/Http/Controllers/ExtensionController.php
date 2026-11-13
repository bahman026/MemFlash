<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Card;
use App\Services\WordListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

/**
 * The MemFlash Chrome extension (extension/ in this repository): its download
 * page, the zip, and the one call it needs that the site's lookup does not.
 *
 * The extension has no sign-in of its own. It calls the site with the
 * browser's MemFlash session cookie, so saving a word goes through the same
 * endpoints as the lookup panel (POST /api/word-lists/words and friends).
 */
class ExtensionController extends Controller
{
    /** The folder inside the zip, so unzipping makes one tidy directory. */
    private const ZIP_FOLDER = 'memflash-extension';

    public function __construct(private readonly WordListService $lists) {}

    /** GET /extension: what it does and how to install it. Public. */
    public function page(): View
    {
        return view('pages.extension', ['version' => $this->version()]);
    }

    /**
     * GET /extension/download
     *
     * Zipped on request from extension/, so the download can never lag
     * behind the code that was deployed.
     */
    public function download(): BinaryFileResponse
    {
        $source = base_path('extension');
        $zipPath = tempnam(sys_get_temp_dir(), 'memflash-extension-');

        $zip = new ZipArchive;
        if ($zipPath === false || $zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the extension zip.');
        }

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS));

        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && ! str_starts_with($file->getFilename(), '.')) {
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
                $zip->addFile($file->getPathname(), self::ZIP_FOLDER . '/' . $relative);
            }
        }

        $zip->close();

        return response()
            ->download($zipPath, 'memflash-extension-' . $this->version() . '.zip', ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend();
    }

    /**
     * GET /api/extension/session
     *
     * Who is signed in, a CSRF token for the extension's POSTs, the lists
     * and the newest words of the list picked last. Answers signed_in: false
     * instead of redirecting to the sign-in page, which a script cannot use.
     */
    public function session(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null || $user->isBlocked()) {
            return response()->json(['signed_in' => false]);
        }

        $listId = $this->lists->selectedListIdFor($user);

        $words = $listId === null ? collect() : Card::query()
            ->where('deck_id', $listId)
            ->latest('id')
            ->limit(20)
            ->get(['front', 'back'])
            ->map(fn (Card $card): array => ['front' => $card->front, 'back' => $card->back]);

        return response()->json([
            'signed_in' => true,
            'user' => ['name' => $user->name, 'email' => $user->email],
            'csrf' => csrf_token(),
            'lists' => $this->lists->pickerListsFor($user),
            'list_id' => $listId,
            'default_list_name' => WordListService::DEFAULT_LIST_NAME,
            'words' => $words->values(),
        ]);
    }

    private function version(): string
    {
        $manifest = json_decode((string) file_get_contents(base_path('extension/manifest.json')), true);

        return is_array($manifest) && is_string($manifest['version'] ?? null) ? $manifest['version'] : '1.0.0';
    }
}
