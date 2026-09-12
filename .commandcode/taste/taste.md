## WordPress / Elementor Development

- For WordPress plugin work, expects strict adherence to WordPress coding standards, security best practices (nonces, capability checks, input sanitization, output escaping), and Elementor conventions. Confidence: 0.65
- Explicitly wants plugin code to be "WordPress.org friendly" / pass WP.org plugin review: text domain must match the plugin slug exactly (no stray domains like `xoom` / `xoomcare-core`), no global or conditionally-declared functions or global-namespace pollution (use prefixed, namespaced classes), never pass dynamic strings to translation functions (`__( $label, … )`), and use proper output escaping plus WPCS formatting (`array()` syntax, docblocks, direct-access guard `defined( 'ABSPATH' ) || exit;`). Confidence: 0.6
- Prefers shared, reusable helpers over copy-pasted code — asks where a utility "best" belongs so it can be reused across widgets/modules instead of duplicating it per file. Confidence: 0.5
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

## Build Tooling / Packaging

- Prefers Dart Sass (`sass`) for SCSS compilation rather than the deprecated `node-sass`. Confidence: 0.85
- Wants a minimal, dependency-light npm build system — explicitly no Webpack, Vite, or Gulp — exposing only `npm install`, `npm run dev` (watch `assets/scss` and compile to `assets/css`) and `npm run build` (compile + minify + package). Confidence: 0.8
- Keeps SCSS sources in `assets/scss` compiled to `assets/css`, and expects those source files to stay tracked/committed while build artifacts do not. Confidence: 0.65
- Expects production builds to produce a directly WordPress-installable ZIP at `/dist/<plugin-slug>.zip` containing exactly one plugin root folder (the slug) and excluding development files (`assets/scss`, `node_modules`, `.git`, `package.json`, `package-lock.json`, source maps, tests). Confidence: 0.8
- Insists build tooling never modify or delete their existing source files. Confidence: 0.7
- Prefers established, off-the-shelf npm packages over hand-rolled tooling for routine build steps — explicitly asks to package the plugin with `dir-archiver` rather than a custom/no-dependency archiver script. Confidence: 0.8
- Wants build/packaging logic to live in a dedicated, commented shell script (e.g. `build-zip.sh` invoked from `npm run build`) instead of crammed inline `node -e` one-liners. Confidence: 0.6
- Prefers build steps to exclude dev artifacts (e.g. `.map` source maps) from the package by pattern/name rather than deleting them, so a running `npm run dev` watcher on their machine isn't disturbed. Confidence: 0.55
- Rejects adding extra config files to work around build-environment quirks — explicitly vetoed a project `.npmrc` (`include=dev`) for the `NODE_ENV=production`/`omit=dev` trap and prefers the fix be left out, just noting that `npm install --include=dev` avoids it. Confidence: 0.6
- Prefers strictly scoped, minimal-diff changes: when they ask for a specific artifact (e.g. "only create `build-zip.sh`"), add exactly that and do not also touch or introduce other files (`package.json`, `.npmrc`, etc.) unless explicitly requested. Confidence: 0.7
