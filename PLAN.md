# Index — structure plan

`onelegstudios/index` — beautiful documentation for Livewire/Laravel.

## 1. Prior art surveyed

| Project | What to steal | What to avoid |
|---|---|---|
| **petebishwhip/laradocs** (already in `shape/vendor`) | The whole non-preview surface is proven: `_index.md` folder landing pages, frontmatter routing (`slug:`, `order:`, `hidden:`, `group:`, `badge:`), semver `VersionRegistry` with `_shared/` + aliases, mtime-invalidated cache, `llms.txt`, MCP server, sitemap/SEO, `make:doc` + `lint` + `check` commands | Ships a Blade *theme* you publish and hand-edit; previews are a text macro bolted on top (see §4). 1105-line config. |
| **Fumadocs** | Three-layer split: core (source/nav/search) / UI / content adapter. The **Loader API** — a filesystem-shaped interface any source implements | React-only patterns don't port |
| **VitePress** | Markdown page ≡ component: you drop live components straight in the page. Sub-100ms dev feedback | — |
| **Docusaurus / Starlight** | Numeric filename prefixes (`01-install.md`) stripped from the slug; `_category_.json` per folder for ordering | Plugin-heavy |
| **shadcn/ui docs** | A build-time **registry** that autogenerates each example's snippet + index, so the previewed component and the printed source are one file | Build script is bespoke |
| **LaRecipe** | Simple, popular multi-version story | Static, no live rendering |

**Honest read:** laradocs already does ~80% of the feature list. The three things that justify building Index are:
1. A **UI contract** (Shape / Flux / your own components) rather than a publishable theme.
2. **First-class live Livewire previews** designed into the pipeline, not macro'd on.
3. A **portable bundle** so docs authored in project A serve from site B.

Everything else is table stakes to be rebuilt. Design so those three are cheap and the rest is boring.

## 2. Architecture

Three layers, one package.

```
Sources ──▶ Documents ──▶ Tree ──▶ Markdown pipeline ──▶ UI driver ──▶ HTML
(fs/bundle) (+frontmatter) (nav)   (+ PreviewNode)      (Shape/Flux/own)
```

- **Core** knows nothing about Blade components. It turns a source into a `Tree` of `Document`s.
- **UI** is a driver resolving ~15 named slots to components. Shape is the default driver; Flux and "your own" are view-name maps.
- **Sources** are the seam for "markdown lives elsewhere".

### The central bet: one artifact, two transports

`index:build` compiles a **bundle** — a manifest plus rendered fragments plus assets. This single format serves four of your requirements:

- *Render live in dev, once in production* → dev has no bundle and parses per request; prod reads only the bundle.
- *Docs in another project, published to another site* → `index:publish` writes the bundle to a Laravel disk (local, S3, …). Site B's project points at that disk. Same format, different transport — no separate sync protocol.
- *Marketing files on the same server* → the disk is `local`.
- *Multiple projects on one server* → each project names its own source.

Laradocs solved the remote case with a hosted deploy service. A disk is simpler and you own it.

## 3. Package structure

```
src/
  Index.php                       facade target — registries + runtime API
  IndexServiceProvider.php
  Facades/Index.php

  Projects/
    Project.php                   key, source, route prefix/domain, versions, ui, layout
    ProjectRegistry.php
    DocsContext.php               request-scoped: project + version + locale

  Sources/
    Contracts/Source.php          all(), find(slug), fingerprint()
    FilesystemSource.php
    BundleSource.php              reads a built manifest off a disk
    CompositeSource.php

  Documents/
    Document.php                  slug, frontmatter, raw, headings, previews
    DocumentCollection.php
    Frontmatter.php
    Slug.php                      numeric-prefix stripping, slug: override

  Tree/
    Tree.php  Node.php  TreeBuilder.php
    Ordering.php                  alphabetical default + override chain

  Versions/
    Version.php                   parsed handle: major/minor/patch/pre/stability
    VersionSet.php                sorted; latest/stable/next aliases
    VersionResolver.php

  Markdown/
    Pipeline.php                  CommonMark environment factory
    Extension/{Preview,Callout,Tabs,HeadingAnchor,LinkRewrite}Extension.php
    Node/PreviewNode.php
    Toc/TableOfContents.php       built from markdown headings only

  Previews/
    Preview.php                   descriptor: file|inline source, props, layout, dynamic?
    PreviewRegistry.php
    PreviewRenderer.php
    PreviewContext.php            bindable: errors bag, auth user, seed data
    Sandbox.php                   guards for the playground tier

  Ui/
    Contracts/UiDriver.php
    UiManager.php  Slots.php
    ShapeDriver.php  BladeDriver.php

  Build/
    Builder.php  Manifest.php  BundleWriter.php  BundleReader.php

  Http/
    Controllers/{Page,Search,Asset}Controller.php
    Middleware/{ResolveProject,ResolveVersion}.php

  Console/Commands/
    InstallCommand.php     MakeDocCommand.php    BuildCommand.php
    ClearCommand.php       CheckCommand.php      PreviewCommand.php
    PublishBundleCommand.php                     VersionCommand.php

  Search/
    Contracts/SearchEngine.php  JsonSearchEngine.php  IndexBuilder.php

resources/views/
  layouts/standalone.blade.php    fallback when the app has no layout
  page.blade.php
  ui/shape/*.blade.php            Shape driver slot implementations
  ui/blade/*.blade.php            dependency-free fallbacks
config/index.php
routes/index.php
```

## 4. Live code — the differentiator

Shape's current `docs-preview.blade.php` is a catalogue of what goes wrong when previews are a text macro on a docs package that wasn't built for them. Every one of its workarounds becomes a design requirement:

| Its workaround | Why | Index's answer |
|---|---|---|
| Output collapsed to one line, newlines as `&#10;` | Macro expands *before* CommonMark parses; a raw HTML block ends at the first blank line | Previews are a **node in the AST**, rendered after parsing. Blank lines are irrelevant. |
| Stylesheet pushed into laradocs' `head` via a View composer | GFM escapes a raw `<style>` | Previews **declare asset requirements**; the page collects them into `head`. |
| `<h2>` rewritten to `role="heading"` | Preview headings polluted the page TOC | TOC is built from **markdown headings only**, before previews render. Structurally impossible. |
| `view()->share('errors', …)` | `x-shape::error` reads the shared error bag | `PreviewContext` — a bindable object supplying errors, auth user, seed data. |
| Stage classes hardcoded in the workbench provider | No component for the chrome | The stage is a **UI-driver slot**, so Shape/Flux/your own each style it. |

### Three tiers

1. **`code`** — fenced block, highlighted. Use [`phiki/phiki`](https://packagist.org/packages/phiki/phiki) (PHP-native Shiki port, TextMate grammars, zero deps, no Node in the build). Laradocs defaults to client-side `shiki-css`; server-side is better for a pre-built bundle.
2. **`preview`** — render a real Blade or Livewire component and print its exact source. Two authoring forms:

   ```` 
   ```blade preview layout=stack
   <x-shape::button>Save</x-shape::button>
   ```
   ````
   ```
   :::preview name=button layout=row
   ```
   The second resolves `docs/previews/button.blade.php` — one file is both the example and the picture of it, the shadcn registry idea.
3. **`playground`** — editable source re-rendered by a Livewire component. **Rendering arbitrary Blade is RCE.** Default off; dev-only unless the app opts into a sandbox with an allowlisted component set. Make this an explicit boundary in the config, not a footnote.

### Livewire in the bundle

A Blade-only preview pre-renders to HTML at build time. A **Livewire preview cannot** — it needs a fresh snapshot and checksum per request. So the manifest marks such nodes dynamic and the cached HTML carries a placeholder (`<!--index:preview:{id}-->`) swapped in at render time. Pure-prose pages — the overwhelming majority — stay a single string read from the manifest.

## 5. Navigation, ordering, versions

### Menu mirrors folders

```
docs/
  getting-started/          ← layer 1
    installation.md         ← layer 2
  components/
    forms/                  ← layer 2
      input.md              ← layer 3+ → nested sidebar groups
```

`navigation.primary` = `sidebar` (default) or `top`. When both are on, layer 1 is the top nav and layer 2 opens the sidebar — exactly your two-layer rule.

### Ordering precedence

1. `order:` in a page's frontmatter.
2. `children:` array in the folder's `_index.md` — explicit sequence by slug.
3. `order:` in the folder's `_index.md` — positions the folder itself.
4. Global map in `config/index.php` (for docs you don't control).
5. Numeric filename prefix (`01-install.md`), stripped from the slug.
6. Alphabetical by title, natural sort.

Settings-file *and* in-markdown, as you asked, with one documented chain.

### Versions

Directory layer above the tree, or explicit config. Handles parse semver-ishly so `0.x`, `1.3.x`, `2.0`, `v3.0.0-beta.1` all sort correctly, stable before prerelease. Aliases `latest` / `stable` / `next`. A `_shared/` directory for pages common to every version, and `since:` / `until:` frontmatter to drop a page outside a range. Unversioned URLs either redirect to the default or serve it — config.

## 6. Layout and UI

- Published sites use the **app layout by default**: `layout => 'components.layouts.app'`, falling back to `index::layouts.standalone` when that view doesn't exist. Detect with `view()->exists()` at boot so a fresh install works with no config.
- The docs chrome (sidebar, TOC, search, version picker, preview stage) is the ~15-slot contract. `ShapeDriver` is the default; a Flux driver or your own is a view-name map. **Keep `onelegstudios/shape` in `suggest`, not `require`** — the Shape driver activates only when the package is present, and `ui/blade/*` covers everyone else. Same for `livewire/livewire`.

## 7. Workbench live preview

Shape wires this by hand in `WorkbenchServiceProvider`. Index ships it as one command:

```bash
php artisan index:preview        # or composer serve in the package
```

Boots testbench, points the source at the package's own `docs/`, disables the cache, opens the browser. Any package author gets a live docs preview for free — that alone is a reason to install Index.

## 8. Phasing

| Phase | Ships |
|---|---|
| 0 | Decide: package split, highlighter, Livewire optionality, playground policy |
| 1 | Single project, single version, filesystem source, live render, Shape UI, folder nav, ordering chain |
| 2 | **Previews** — AST node, asset collection, `PreviewContext`, stage slot, Livewire support |
| 3 | `index:build`, manifest, bundle, prod read path, `index:publish` to a disk |
| 4 | Versions — parser, sorting, aliases, `_shared`, picker, `since:`/`until:` |
| 5 | Multi-project routing, `index:preview` workbench command, `index:check` |
| 6 | Search, `llms.txt` / MCP, i18n, OG images |

Phase 2 before phase 3 deliberately: the preview design constrains the bundle format, so build the thing that has opinions first.

## 9. Open decisions

1. **One package or three?** Recommend one, with drivers. Split `index-core` / `index-shape` only if Flux users object to the dependency — which `suggest` already prevents.
2. **Playground security policy** — dev-only, sandboxed allowlist, or off. Decide before writing it.
3. **Highlighter** — Phiki (server-side, PHP) vs Torchlight (hosted) vs `shiki-css` (client-side). Phiki suits a pre-built bundle best.
4. **Does the bundle carry assets?** If yes, `index:publish` moves CSS/images too and site B needs no build step. If no, site B builds Tailwind over the docs. The first is more useful and more work.
5. **Frontmatter vocabulary** — worth copying laradocs' verbatim (`title`, `description`, `order`, `hidden`, `group`, `badge`, `redirect`, `tags`, `slug`) so anyone migrating just moves files.
