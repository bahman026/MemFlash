# Component Layer Structure

This directory contains all Blade components organized by their purpose and functionality.

## Directory Structure

```
components/
├── ui/                          # UI Components
│   ├── buttons/                 # Button Components
│   │   ├── action-button.blade.php
│   │   ├── back-to-dashboard.blade.php
│   │   └── edit-deck.blade.php
│   ├── cards/                   # Card Components
│   │   └── deck-card.blade.php
│   ├── forms/                   # Form Components
│   │   ├── input.blade.php
│   │   └── textarea.blade.php
│   ├── modals/                  # Modal Components
│   │   └── deck-create-modal.blade.php
│   ├── navigation/              # Navigation Components
│   │   └── profile-dropdown.blade.php
│   └── feedback/                # Feedback Components
│       └── alert.blade.php
├── layouts/                     # Layout Components
│   ├── sections/                # Layout Sections
│   │   ├── header.blade.php
│   │   ├── footer.blade.php
│   │   └── simple-footer.blade.php
│   ├── partials/                # Layout Partials
│   │   └── header-actions.blade.php
│   └── app.blade.php            # Main Layout
├── deck.blade.php               # Deck Layout
└── static-deck.blade.php        # Static Deck Layout
```

## Component Naming Convention

- **UI Components**: `x-ui.{category}.{component-name}`
- **Layout Components**: `x-layouts.{section}.{component-name}`
- **Direct Components**: `x-{component-name}`

## Usage Examples

### UI Components

```blade
<!-- Buttons -->
<x-ui.buttons.back-to-dashboard />
<x-ui.buttons.edit-deck :deck="$deck" />
<x-ui.buttons.action-button href="/custom" title="Custom Action" />

<!-- Cards -->
<x-ui.cards.deck-card :deck="$deck" type="user" />

<!-- Forms -->
<x-ui.forms.input name="title" label="Deck Title" required />
<x-ui.forms.textarea name="description" label="Description" rows="3" />

<!-- Feedback -->
<x-ui.feedback.alert type="success" dismissible>
    Deck created successfully!
</x-ui.feedback.alert>

<!-- Navigation -->
<x-ui.navigation.profile-dropdown :user="auth()->user()" />
```

### Layout Components

```blade
<!-- Layout Sections -->
<x-layouts.sections.header :title="'My App'" />
<x-layouts.sections.simple-footer />

<!-- Layout Partials -->
<x-layouts.partials.header-actions>
    <x-ui.buttons.back-to-dashboard />
</x-layouts.partials.header-actions>

<!-- Main Layouts -->
<x-layouts.app :header-title="'Dashboard'">
    <x-slot name="headerActions">
        <x-ui.buttons.action-button href="/settings" title="Settings" />
    </x-slot>
    
    Content goes here...
</x-layouts.app>
```

## Component Props

Each component accepts specific props for customization. Check individual component files for detailed prop documentation.

## Best Practices

1. **Consistency**: Use the same styling patterns across similar components
2. **Reusability**: Create generic components that can be used in multiple contexts
3. **Props**: Use props for customization instead of hardcoding values
4. **Accessibility**: Include proper ARIA labels and keyboard navigation
5. **Responsive**: Ensure components work well on all screen sizes
