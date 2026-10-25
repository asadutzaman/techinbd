/*
 * Storefront behaviour shared by every page: the drawers (phone menu, shop filters), the phone search
 * toggle, the category bar's menu placement, the product rails' scroll buttons, tabs, the product cards'
 * Add to cart, compare and wishlist buttons, and the account forms' Show / Hide password buttons.
 * Plain JS; loaded at the end of the layout.
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    // ---------- Drawers: the phone menu and the shop's filter panel ----------
    // A [data-drawer-open] button opens the [data-drawer] its aria-controls names; [data-drawer-close] closes it.
    // A drawer only exists below its data-drawer-until width (1200px unless set): past that the menu hides and the
    // filters sit in the page, so an open drawer closes, and the filters only act as a dialog while open.
    var drawer = null;
    var opener = null;

    function focusables(container) {
        return Array.prototype.filter.call(
            container.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), summary'),
            function (el) { return el.offsetParent !== null; }
        );
    }

    function setExpanded(target, expanded) {
        document.querySelectorAll('[data-drawer-open][aria-controls="' + target.id + '"]').forEach(function (button) {
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    }

    function openDrawer(target, button) {
        drawer = target;
        opener = button;
        drawer.classList.add('is-open');
        if (drawer.hasAttribute('data-drawer-until')) {
            drawer.setAttribute('role', 'dialog');
            drawer.setAttribute('aria-modal', 'true');
        }
        document.body.classList.add('sf-drawer-open');
        setExpanded(drawer, true);
        var body = drawer.querySelector('[data-drawer-body]');
        var first = body && focusables(body)[0];
        (first || drawer.querySelector('button[data-drawer-close]')).focus();
    }

    function closeDrawer(returnFocus) {
        drawer.classList.remove('is-open');
        if (drawer.hasAttribute('data-drawer-until')) {
            drawer.removeAttribute('role');
            drawer.removeAttribute('aria-modal');
        }
        document.body.classList.remove('sf-drawer-open');
        setExpanded(drawer, false);
        if (returnFocus && opener) {
            opener.focus();
        }
        drawer = null;
        opener = null;
    }

    document.addEventListener('click', function (event) {
        var open = event.target.closest('[data-drawer-open]');
        var target = open && document.getElementById(open.getAttribute('aria-controls'));
        if (target) {
            openDrawer(target, open);
            return;
        }

        var close = event.target.closest('[data-drawer-close]');
        if (close && drawer && drawer.contains(close)) {
            // A menu item that opens a dialog (Compare) hands focus to that dialog instead
            closeDrawer(!close.hasAttribute('data-toggle'));
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!drawer) {
            return;
        }

        if (event.key === 'Escape') {
            closeDrawer(true);
            return;
        }

        // Keep Tab inside the open drawer
        if (event.key === 'Tab') {
            var items = focusables(drawer);
            var first = items[0];
            var last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    document.querySelectorAll('[data-drawer]').forEach(function (target) {
        var wide = window.matchMedia('(min-width: ' + (target.getAttribute('data-drawer-until') || 1200) + 'px)');
        wide.addEventListener('change', function (query) {
            if (query.matches && drawer === target) {
                closeDrawer(false);
            }
        });
    });

    // ---------- Sticky header height ----------
    // Published as --sf-sticky-top, so other sticky bars (the product page tabs) sit right under the header
    var header = document.querySelector('.sf-header');
    if (header && 'ResizeObserver' in window) {
        new ResizeObserver(function () {
            document.documentElement.style.setProperty('--sf-sticky-top', header.offsetHeight + 'px');
        }).observe(header);
    }

    // ---------- Phone search ----------
    var searchToggle = document.querySelector('[data-search-toggle]');
    if (header && searchToggle) {
        searchToggle.addEventListener('click', function () {
            var open = header.classList.toggle('is-search-open');
            searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) {
                document.getElementById('search-input').focus();
            }
        });
    }

    // ---------- Category bar ----------
    // A menu or brand list that would run off the right of the screen opens leftwards instead. Measured as each
    // category is reached, so it follows the window's width and the menu's real size.
    document.querySelectorAll('.sf-catbar-list > li.has-menu').forEach(function (item) {
        function place() {
            item.querySelectorAll('.sf-catbar-menu, .sf-catbar-flyout').forEach(function (menu) {
                menu.classList.remove('is-flipped');
                if (menu.getBoundingClientRect().right > document.documentElement.clientWidth - 8) {
                    menu.classList.add('is-flipped');
                }
            });
        }

        item.addEventListener('pointerenter', place);
        item.addEventListener('focusin', function (event) {
            // Once per visit, not on every Tab inside it
            if (!item.contains(event.relatedTarget)) {
                place();
            }
        });
    });

    // ---------- Product rails ----------
    document.querySelectorAll('[data-rail]').forEach(function (section) {
        var rail = section.querySelector('.sf-rail');
        var prev = section.querySelector('[data-rail-prev]');
        var next = section.querySelector('[data-rail-next]');
        if (!rail || !prev || !next) {
            return;
        }

        function update() {
            prev.disabled = rail.scrollLeft <= 1;
            next.disabled = rail.scrollLeft >= rail.scrollWidth - rail.clientWidth - 1;
        }

        function scroll(direction) {
            rail.scrollBy({ left: direction * rail.clientWidth, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
        }

        prev.addEventListener('click', function () { scroll(-1); });
        next.addEventListener('click', function () { scroll(1); });
        rail.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });

    // ---------- Tabs ([data-tabs]: role="tab" buttons and the role="tabpanel" each one controls) ----------
    // Home page Trending deals. Clicking a tab or moving along them with the arrow keys, Home and End shows its
    // panel; a [data-tabs-more] link follows the open tab's data-more-url.
    document.querySelectorAll('[data-tabs]').forEach(function (container) {
        var tabs = Array.prototype.slice.call(container.querySelectorAll('[role="tab"]'));
        var more = container.querySelector('[data-tabs-more]');
        var moreLabel = more && more.querySelector('[data-tabs-more-label]');

        function select(tab, focus) {
            tabs.forEach(function (other) {
                var selected = other === tab;
                other.setAttribute('aria-selected', selected ? 'true' : 'false');
                other.tabIndex = selected ? 0 : -1;
                document.getElementById(other.getAttribute('aria-controls')).hidden = !selected;
            });
            if (more && tab.dataset.moreUrl) {
                more.href = tab.dataset.moreUrl;
            }
            if (moreLabel && tab.dataset.moreLabel) {
                moreLabel.textContent = tab.dataset.moreLabel;
            }
            // The panel's row could only measure itself for its arrows once it shows
            var rail = document.getElementById(tab.getAttribute('aria-controls')).querySelector('.sf-rail');
            if (rail) {
                rail.dispatchEvent(new Event('scroll'));
            }
            if (focus) {
                tab.focus();
            }
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { select(tab, false); });
            tab.addEventListener('keydown', function (event) {
                var target = { ArrowRight: index + 1, ArrowLeft: index - 1, Home: 0, End: tabs.length - 1 }[event.key];
                if (target === undefined) {
                    return;
                }
                event.preventDefault();
                select(tabs[(target + tabs.length) % tabs.length], true);
            });
        });
    });

    // ---------- Product cards (components/product-card.blade.php) ----------
    var csrfToken = document.querySelector('meta[name="csrf-token"]');

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.sf-card .add-to-cart-btn');
        if (!button || button.disabled) {
            return;
        }

        var label = button.innerHTML;
        button.disabled = true;
        button.textContent = 'Adding…';

        var data = new FormData();
        data.append('product_id', button.dataset.productId);
        data.append('quantity', '1');

        fetch(button.dataset.cartUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '', 'Accept': 'application/json' },
            body: data,
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (json) {
                if (!response.ok) {
                    // Validation messages are written for shoppers; other errors aren't
                    throw new Error(response.status === 422 && json.message ? json.message : '');
                }
                return json;
            });
        }).then(function (json) {
            toastr.success(json.message);
            window.setCartCount(json.cart_count);
        }).catch(function (error) {
            toastr.error(error.message || 'Could not add this product to your cart. Try again.');
        }).then(function () {
            button.disabled = false;
            button.innerHTML = label;
        });
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-compare-id]');
        if (button && typeof window.addToCompare === 'function') {
            window.addToCompare(Number(button.dataset.compareId), button.dataset.compareName, Number(button.dataset.comparePrice), button.dataset.compareImage);
        }
    });

    // The home cards' hearts (signed in; guests get a link to sign in): every card of the product follows, and the
    // header's wishlist badges take the new count
    document.addEventListener('click', function (event) {
        var button = event.target.closest('button.sf-card-wish[data-wishlist-url]');
        if (!button || button.disabled) {
            return;
        }

        button.disabled = true;
        var data = new FormData();
        data.append('product_id', button.dataset.productId);

        fetch(button.dataset.wishlistUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '', 'Accept': 'application/json' },
            body: data,
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) {
                throw new Error();
            }
            return response.json();
        }).then(function (json) {
            document.querySelectorAll('button.sf-card-wish[data-product-id="' + button.dataset.productId + '"]').forEach(function (heart) {
                heart.setAttribute('aria-pressed', json.in_wishlist ? 'true' : 'false');
                heart.querySelector('i').className = (json.in_wishlist ? 'fas' : 'far') + ' fa-heart';
            });
            document.querySelectorAll('.wishlist-count').forEach(function (badge) {
                badge.textContent = json.wishlist_count;
                badge.hidden = !json.wishlist_count;
            });
            toastr.success(json.message);
        }).catch(function () {
            toastr.error('Could not update your wishlist. Try again.');
        }).then(function () {
            button.disabled = false;
        });
    });

    // ---------- Password fields: Show / Hide ----------
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-password-toggle]');
        var input = toggle && document.getElementById(toggle.getAttribute('aria-controls'));
        if (!input) {
            return;
        }

        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
})();
