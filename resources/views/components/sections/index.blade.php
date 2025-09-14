{{--
    Section Components Index
    
    This file documents the available section components for the application.
    
    Available Components:
    - hero.blade.php: Hero section with title, subtitle, CTA buttons, and demo card
    - demo-card.blade.php: Interactive demo card component
    - features.blade.php: Features section with configurable feature cards
    - cta.blade.php: Call-to-action section component
    
    Usage Examples:
    
    Hero Section:
    <x-sections.hero 
        :title="'Your Custom Title'"
        :subtitle="'Your custom subtitle'"
        :show-demo-card="true"
        :show-cta-buttons="true"
    />
    
    Demo Card:
    <x-sections.demo-card 
        :card-number="'1'"
        :total-cards="'25'"
        :question="'What is the capital of France?'"
        :difficulty="'Easy'"
    />
    
    Features Section:
    <x-sections.features 
        :title="'Why Choose Our Product?'"
        :subtitle="'Our amazing features...'"
        :features="[
            ['icon' => '...', 'title' => 'Feature 1', 'description' => '...'],
            // ... more features
        ]"
    />
    
    CTA Section:
    <x-sections.cta 
        :title="'Ready to Get Started?'"
        :subtitle="'Join thousands of users...'"
        :button-text="'Get Started'"
        :button-url="'/signup'"
    />
--}}
