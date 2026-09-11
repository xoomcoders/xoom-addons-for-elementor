## WordPress / Elementor Development

- For WordPress plugin work, expects strict adherence to WordPress coding standards, security best practices (nonces, capability checks, input sanitization, output escaping), and Elementor conventions. Confidence: 0.65
- Expects plugin frontend assets to be loaded only when actually needed — CSS/JS should be registered conditionally and never enqueued on pages where the relevant widget/module isn't used. Confidence: 0.6

## UI / Product Quality

- Wants admin/dashboard UIs to feel like a high-quality commercial product: clean, modern, polished, fast, and intuitive. Confidence: 0.6
- Expects interfaces to be responsive and accessible (a11y). Confidence: 0.55
- Never wants native browser dialogs (`alert()`/`confirm()`/`prompt()`) in product UI — even `beforeunload` prompts are off-limits. Confirmable/destructive actions should use the product's own confirmation UI (an anchored confirmation popover or inline confirmation panel) styled to match the existing design system. Confidence: 0.8
- Prefers destructive or bulk changes to be staged with an explicit confirm step (e.g. a sticky bottom action bar with Cancel / Save changes that previews the outcome), while routine single-item changes apply immediately with lightweight, non-blocking toast feedback rather than a dialog. Confidence: 0.7
- Expects new UI to be built within the existing design system — reusing its CSS custom-property tokens, class-naming conventions and component patterns rather than introducing a new framework or one-off styles — with clean typography, subtle borders, rounded corners, proper spacing, and smooth but minimal transitions. Confidence: 0.65

## Architecture

- Prefers scalable, component-based architecture where declarative registries are the single source of truth, so new widgets/settings/features (Free/Pro, extensions, global styles, asset optimization, licensing) can be added without a major rewrite. Confidence: 0.6
- Prefers Free/Pro split as two separate WordPress plugins (the Pro add-on requires the Free one) over a single codebase gated by a licence, so the Free plugin ships only free code and stays WordPress.org-compliant. Confidence: 0.55
- Wants to be able to test/flip feature gates (e.g. licence state) themselves during development without a real server — favors frictionless local dev toggles such as a mu-plugin filter (`__return_true`) or WP-CLI option changes. Confidence: 0.5
