/**
 * Minimal in-memory IndexedDB for the node tests: exactly the surface
 * resources/js/offline/db.js uses (open + upgrade, transactions, object stores
 * with get/put/delete/clear/getAll/count, and index().getAllKeys(only)).
 *
 * Requests complete asynchronously and a transaction completes once no request
 * is outstanding, including requests made from another request's onsuccess --
 * the part of IndexedDB that db.js relies on and that a naive stub gets wrong.
 */

const clone = (value) => (value === undefined ? undefined : structuredClone(value));
const keyOf = (record, keyPath) =>
    Array.isArray(keyPath) ? keyPath.map((path) => record[path]) : record[keyPath];
const same = (a, b) => JSON.stringify(a) === JSON.stringify(b);

export const FakeKeyRange = { only: (value) => ({ only: value }) };

export function createFakeIndexedDB() {
    const stores = new Map();

    const db = {
        objectStoreNames: { contains: (name) => stores.has(name) },
        createObjectStore(name, { keyPath }) {
            const store = { keyPath, records: new Map(), indexes: new Map() };
            stores.set(name, store);

            return { createIndex: (indexName, indexKeyPath) => store.indexes.set(indexName, indexKeyPath) };
        },
        transaction(storeNames, _mode) {
            return createTransaction(stores, storeNames);
        },
    };

    return {
        /** Wipe every record, keeping the schema, between tests. */
        reset() {
            for (const store of stores.values()) store.records.clear();
        },
        open() {
            const request = { result: db, error: null, onsuccess: null, onerror: null, onupgradeneeded: null };

            queueMicrotask(() => {
                if (stores.size === 0) request.onupgradeneeded?.({ target: request });
                request.onsuccess?.({ target: request });
            });

            return request;
        },
    };
}

function createTransaction(stores, storeNames) {
    const names = Array.isArray(storeNames) ? storeNames : [storeNames];
    let outstanding = 0;
    let finished = false;

    const transaction = { oncomplete: null, onerror: null, onabort: null, error: null };

    const settle = () => {
        queueMicrotask(() => {
            if (outstanding === 0 && !finished) {
                finished = true;
                transaction.oncomplete?.();
            }
        });
    };

    const request = (run) => {
        const req = { result: undefined, error: null, onsuccess: null, onerror: null };
        outstanding++;

        queueMicrotask(() => {
            try {
                req.result = run();
                req.onsuccess?.({ target: req });
            } catch (error) {
                req.error = error;
                transaction.error = error;
                req.onerror?.({ target: req });
                transaction.onerror?.();
            } finally {
                outstanding--;
                settle();
            }
        });

        return req;
    };

    transaction.objectStore = (name) => {
        if (!names.includes(name)) throw new Error(`Store ${name} is not in this transaction`);
        const store = stores.get(name);

        return {
            put: (record) => request(() => store.records.set(keyOf(record, store.keyPath), clone(record)) && undefined),
            get: (key) => request(() => clone(store.records.get(key))),
            delete: (key) => request(() => store.records.delete(key) && undefined),
            clear: () => request(() => store.records.clear()),
            getAll: () => request(() => [...store.records.values()].map(clone)),
            count: () => request(() => store.records.size),
            index: (indexName) => ({
                getAllKeys: (range) =>
                    request(() => {
                        const indexKeyPath = store.indexes.get(indexName);

                        return [...store.records.entries()]
                            .filter(([, record]) => same(keyOf(record, indexKeyPath), range.only))
                            .map(([key]) => key);
                    }),
            }),
        };
    };

    // A transaction with no requests at all still completes.
    settle();

    return transaction;
}
