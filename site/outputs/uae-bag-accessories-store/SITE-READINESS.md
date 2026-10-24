# Almasar Al Raed — local site readiness review

Review date: 22 October 2026  
Scope: bundled WooCommerce theme/plugin source, the local WordPress site at `http://localhost/almasar/`, and a read-only check of `https://almasaralraed.com/` on the Cloudflare `almasar-store-preview` Worker. This is a code and content review, not a legal opinion, penetration test, or full production-host audit.

## Changes in this pass

- Brought the Gadgets collection back onto the same paper, sand, olive, and ink palette as Bags. Blue now marks only a few small Gadget details.
- Removed the retired “Atelier Edit” links and filter option. Old English and Arabic paths now redirect to the Bags collection and stay out of search indexing.
- Added accessible names to search fields in the static preview.
- Raised the preview's HSTS max-age to one year. Keep it only while the host serves the whole site over HTTPS.
- Synced the latest theme and plugin source to the local WAMP WordPress installation, then activated the updated `uae-bag-boutique` theme. The previous local theme/plugin files are backed up at `outputs/uae-bag-accessories-store/local-preview-backup-2026-10-22`.
- Verified the Cloudflare storefront already shows separate Bags and Gadgets collections, category/color/size/brand filters, and “New Gadgets”; the retired “Atelier Edit” is absent. No new public deployment was made because the existing public version already shows those updates.

## What the current files show

- The Cloudflare Worker uploader states that it accepts static HTML/CSS/JS assets only; the service has zero Worker bindings. The public site therefore remains a static storefront preview, not a connected WooCommerce backend. Its product-category pages have no actual product listings yet. Do not treat its account, cart, order, payment, or inventory interfaces as operational until a dynamic backend is connected and verified.
- No analytics, ad-pixel, video/map embed, or externally hosted JavaScript was found in the scoped preview. Google Fonts CSS is loaded from `fonts.googleapis.com` / `fonts.gstatic.com`; that makes a third-party network request when a page loads. WhatsApp and Instagram are outbound links.
- No customer reviews, testimonials, star ratings, or quantified satisfaction claims were found in the scoped HTML.
- Empty shop, account, cart, and category routes are `noindex`; the sitemap lists only the English and Arabic home pages. Add real product/category URLs and remove their `noindex` only after the catalog is populated and checked.
- The static build has an `_headers` file configured for CSP, `nosniff`, referrer policy, frame protection, permissions policy, and HSTS. These settings still need to be confirmed on actual Cloudflare responses; a local header file alone does not prove the host applied them. WAMP does not apply Cloudflare's `_headers` rules.
- The custom WordPress plugin source includes a manager capability check for its settings, settings sanitization, escaping for rendered output, and nonce/capability checks on category edits. It also enables WooCommerce account registration on first run. This source is not the backend serving the current static preview, and no Google OAuth or custom login-attempt throttling is present in that plugin.

## Items that cannot be truthfully finalized yet

- Privacy, cookie, terms, and refund pages need the real licensed business name, address, licensing authority, support contact, actual order/data workflow, retention period, and the return/refund rules. These were not supplied, so this review does not publish invented policy text.
- The store targets the UAE and other countries. A UAE business/legal review is needed for the actual cross-border sales destinations, Arabic product/checkout information, invoices, consumer remedies, and privacy obligations. The UAE government's e-commerce overview points to Federal Decree-Law No. 14 of 2023; its consumer-protection overview describes requirements for licensed e-commerce providers and Arabic information. See [UAE eCommerce](https://u.ae/en/information-and-services/business/ecommerce/) and [UAE Consumer Protection](https://u.ae/en/information-and-services/justice-safety-and-the-law/consumer-protection).
- No consent banner was added because the scoped static preview contains no identified analytics or advertising tracker and does not set a client-side tracking cookie. This is not a conclusion for the live WooCommerce backend: it can set cart/session cookies, and the PHP source sets a language-preference cookie. Inventory the live host, checkout, plugins, CDN, and visitor regions before deciding on consent controls.
- The requested ZYRO, Felidae, Gestur Morise, Agraham, Satyr SP 2, and Lama Rounded font files are not bundled in the project. Some third-party listings describe selected versions as commercial-use, but no corresponding foundry license files were supplied. Do not embed a font in the store until its exact file and web-embedding license are verified. The free Agraham demo is personal-use only; the foundry offers a separate webfont license for website embedding. See [Hishand Studio's FAQ](https://hishandstudio.com/faq/) and the [Agraham webfont listing](https://www.myfonts.com/collections/agraham-font-hishand-studio?tab=licensing).
- Category artwork and the uploaded logo are present, but this project does not contain a complete rights/provenance register. Confirm ownership or commercial-use rights for each image and font before launch.
- WCAG 2.2 AA remains a target, not a certified pass. Search names, keyboard-native disclosure menus, visible focus styling, and reduced-motion rules exist in source. Full contrast measurements, screen-reader checks, mobile reflow/zoom, checkout keyboard use, and a rendered-browser review remain outstanding.
- A Codex Security source scan reviewed 25 files and ended with zero validated findings. PHP syntax checks passed for the nine PHP files in that scan. This does not replace a live-host penetration test or configuration review.

## Release gates

1. Connect and verify a real WooCommerce backend before enabling customer accounts, Google OAuth, checkout, stock, bank-transfer reservations, or order notifications. Configure Google OAuth with server-side credentials and enforce login/registration abuse controls at the host/WAF; never put client secrets in browser assets.
2. Confirm Cloudflare deployment headers, HTTPS behavior, backups, and a restore procedure on the actual host.
3. Load the real product catalog and images; verify SKU updates, Arabic/English fields, variants, stock, prices, filters, and shipping fees. Then update the sitemap and indexable metadata.
4. Supply business identity, contacts, returns/refunds, destinations, payments, analytics/data-storage, and retention details; have UAE counsel review the final policies and international consumer/privacy coverage.
5. Verify font and image licenses, then complete rendered accessibility and mobile review against [WCAG 2.2](https://www.w3.org/TR/WCAG22/).
6. Submit the verified sitemap to Google Search Console and Bing Webmaster Tools only after the production pages are populated and indexable.

The connected plugin catalog did not return AVSBL Website Builder or regards.design. The local WAMP preview was updated directly; Cloudflare was inspected, and its public storefront already reflected the requested collection split and “New Gadgets” naming, so no additional public deployment was needed.
