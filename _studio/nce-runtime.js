(function() {
    var origin = "https://studiooo1.framer.website";
    var allowed = ["https://studiooo1.framer.website/", "https://studiooo1.framer.website/404"];
    var page = window.__NCE_PAGE__ || { path: "/", root: "./" };
    var relativeRoot = page.root || "./";
    var pageBase = origin + (page.path || "/");

    // www-insensitive same-site check, mirroring the server-side crawler:
    // canonical-host links (www vs bare domain) are still internal.
    function siteHost(host) {
        var h = (host || "").toLowerCase();
        return h.indexOf("www.") === 0 ? h.slice(4) : h;
    }
    var exportHost = siteHost(origin.replace(/^https?:\/\//, ""));
    function isInternalUrl(url) { return siteHost(url.host) === exportHost; }

    function normalizePath(pathname) {
        var withoutIndex = pathname.replace(/\/index\.html$/i, "");
        var withoutTrailingSlash = withoutIndex.replace(/\/$/, "");
        return withoutTrailingSlash || "/";
    }

    var currentPath = normalizePath(page.path || "/");

    function buildExportHref(url) {
        var clean = normalizePath(url.pathname);
        if (clean === currentPath && url.hash) {
            return url.hash;
        }

        var targetPath = clean === "/" ? "index.html" : clean.substring(1) + "/index.html";
        return relativeRoot + targetPath + (url.hash || "");
    }

    function neutralizeLink(a) {
        if (!a) return;
        var rawHref = a.getAttribute("href");
        if (!rawHref) return;
        if (a.getAttribute("data-nce-checked") === rawHref) return;

        a.setAttribute("data-nce-checked", rawHref);

        // Ignore anchors, JS, mailto, tel
        if (rawHref.indexOf("#") === 0 || rawHref.indexOf("javascript:") === 0 || rawHref.indexOf("mailto:") === 0 || rawHref.indexOf("tel:") === 0) return;

        try {
            // Crucial: Resolve against intended page URL, NOT local filesystem.
            var url = new URL(rawHref, pageBase);
            if (isInternalUrl(url)) {
                // Internal link
                var clean = normalizePath(url.pathname);
                var full = origin + clean;
                var isExported = allowed.indexOf(full) !== -1;

                if (!isExported) {
                    // Block unexported pages
                    a.setAttribute("href", "javascript:void(0)");
                    a.removeAttribute("target");
                    a.style.cursor = "default";
                } else {
                    a.setAttribute("href", buildExportHref(url));
                    if (a.style.cursor === "default") {
                        a.style.cursor = "";
                    }
                }
            } else {
                // External link
                if (a.getAttribute("target") !== "_blank") {
                    a.setAttribute("target", "_top");
                }
            }
        } catch(err) {}
    }

    // Process existing links immediately
    document.querySelectorAll("a[href]").forEach(neutralizeLink);

    // Watch for dynamically added links (e.g., React portals, dropdowns)
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // ELEMENT_NODE
                    if (node.tagName === "A") neutralizeLink(node);
                    if (node.querySelectorAll) {
                        node.querySelectorAll("a[href]").forEach(neutralizeLink);
                    }
                }
            });
            if (mutation.type === "attributes" && mutation.attributeName === "href") {
                neutralizeLink(mutation.target);
            }
        });
    });
    // Target documentElement to catch portals/overlays injected outside <body>
    observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ["href"] });

    // Fallback event blackhole for advanced SPAs targeting unexported links
    var events = ["click", "mousedown", "mouseup", "pointerdown", "pointerup", "touchstart", "touchend"];
    events.forEach(function(ev) {
        document.addEventListener(ev, function(e) {
            var a = e.target.closest("a");
            if (!a) return;
            var rawHref = a.getAttribute("href");
            if (!rawHref || rawHref.indexOf("#") === 0) return;

            // Blackhole neutralized links completely across all interactions
            if (rawHref.indexOf("javascript:void(0)") !== -1) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return;
            }

            // Offline file:// routing fallback
            if (ev === "click" && window.location.protocol === "file:") {
                try {
                    var url = new URL(rawHref, pageBase);
                    if (isInternalUrl(url)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();

                        var clean = normalizePath(url.pathname);
                        var full = origin + clean;
                        var isExported = allowed.indexOf(full) !== -1;
                        if (isExported) {
                            if (clean === currentPath && url.hash) {
                                var el = document.querySelector(url.hash);
                                if (el) { el.scrollIntoView({ behavior: 'smooth' }); }
                                else { window.location.hash = url.hash; }
                                return;
                            }

                            window.location.href = buildExportHref(url);
                        }
                        return;
                    }
                } catch(err) {}
            }
        }, true);
    });
})();
