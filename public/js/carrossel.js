(function () {
	'use strict';

	function initCarrossel(root) {
		var slides = root.querySelectorAll('.vb-prod-carrossel__slide');
		if (slides.length < 2) {
			return;
		}
		var dots = root.querySelectorAll('.vb-prod-carrossel__dot');
		var index = 0;

		function go(to) {
			index = (to + slides.length) % slides.length;
			slides.forEach(function (el, i) {
				el.classList.toggle('is-active', i === index);
			});
			dots.forEach(function (el, i) {
				el.classList.toggle('is-active', i === index);
			});
		}

		var prev = root.querySelector('.vb-prod-carrossel__btn--prev');
		var next = root.querySelector('.vb-prod-carrossel__btn--next');
		if (prev) {
			prev.addEventListener('click', function () { go(index - 1); });
		}
		if (next) {
			next.addEventListener('click', function () { go(index + 1); });
		}
		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				go(parseInt(dot.getAttribute('data-index'), 10) || 0);
			});
		});
	}

	function updateProdCount(n) {
		document.querySelectorAll('[data-vb-prod-count]').forEach(function (el) {
			var num = el.querySelector('.vb-prod-count__n');
			var label = el.querySelector('.vb-prod-count__label');
			if (num) {
				num.textContent = String(n);
			}
			if (label) {
				label.textContent = n === 1 ? 'produto encontrado' : 'produtos encontrados';
			}
		});
	}

	function listaWraps() {
		var wraps = [];
		document.querySelectorAll('[data-vb-prod-lista], [data-vb-paginacao]').forEach(function (lista) {
			var wrap = lista.closest('.vb-prod-lista-wrap') || lista.parentElement;
			if (wrap && wraps.indexOf(wrap) === -1) {
				wraps.push(wrap);
			}
		});
		return wraps;
	}

	function ensureListaLoading(wrap) {
		if (!wrap || wrap.querySelector('[data-vb-lista-loading]')) {
			return;
		}
		var overlay = document.createElement('div');
		overlay.className = 'vb-prod-lista-loading';
		overlay.setAttribute('data-vb-lista-loading', '');
		overlay.setAttribute('aria-hidden', 'true');
		overlay.innerHTML =
			'<div class="vb-prod-lista-loading__box">' +
			'<span class="vb-prod-lista-loading__spinner" aria-hidden="true"></span>' +
			'<span class="vb-prod-lista-loading__text">Carregando…</span>' +
			'</div>';
		wrap.appendChild(overlay);
	}

	function setListaLoading(on) {
		listaWraps().forEach(function (wrap) {
			ensureListaLoading(wrap);
			wrap.classList.toggle('is-loading', !!on);
			var overlay = wrap.querySelector('[data-vb-lista-loading]');
			if (overlay) {
				overlay.setAttribute('aria-hidden', on ? 'false' : 'true');
			}
		});
	}

	function applyListaFiltersAnimated() {
		setListaLoading(true);
		clearTimeout(window.__vbProdFilterLoadT);
		window.__vbProdFilterLoadT = setTimeout(function () {
			applyListaFilters();
			window.setTimeout(function () {
				setListaLoading(false);
			}, 320);
		}, 160);
	}

	function applyListaFilters() {
		var cat = '';
		var marca = '';
		var activeCat = document.querySelector(
			'.vb-prod-cats--categorias .vb-prod-tab.is-active[data-cat], .vb-prod-cats--categorias .vb-prod-cats__item.is-active[data-cat]'
		);
		if (activeCat) {
			cat = activeCat.getAttribute('data-cat') || '';
		}
		var activeMarca = document.querySelector(
			'.vb-prod-cats--marcas .vb-prod-tab.is-active[data-marca], .vb-prod-cats--marcas .vb-prod-cats__item.is-active[data-marca]'
		);
		if (activeMarca) {
			marca = activeMarca.getAttribute('data-marca') || '';
		}
		var q = '';
		var busca = document.querySelector('[data-vb-prod-busca] .vb-prod-busca__input');
		if (busca) {
			q = (busca.value || '').toLowerCase().trim();
		}

		var totalMatch = 0;
		var listas = document.querySelectorAll('[data-vb-prod-lista], [data-vb-paginacao]');
		if (!listas.length) {
			return;
		}

		listas.forEach(function (lista) {
			var any = false;
			var matchItems = [];
			lista.querySelectorAll('.vb-prod-lista__item').forEach(function (item) {
				var cats = (item.getAttribute('data-cats') || '').split(/\s+/);
				var marcas = (item.getAttribute('data-marcas') || '').split(/\s+/);
				var text = (item.getAttribute('data-search') || '').toLowerCase();
				var okCat = !cat || cats.indexOf(cat) !== -1;
				var okMarca = !marca || marcas.indexOf(marca) !== -1;
				var okQ = !q || text.indexOf(q) !== -1;
				var show = okCat && okMarca && okQ;
				item.classList.toggle('is-filtered-out', !show);
				if (show) {
					any = true;
					matchItems.push(item);
				}
			});
			totalMatch += matchItems.length;
			var empty = lista.querySelector('.vb-prod-lista__empty-js');
			if (!any) {
				if (!empty) {
					empty = document.createElement('p');
					empty.className = 'vb-prod-lista__empty vb-prod-lista__empty-js';
					empty.textContent = 'Nenhum produto encontrado.';
					lista.appendChild(empty);
				}
			} else if (empty) {
				empty.remove();
			}
			applyPaginacao(lista, matchItems, true);
			var wrap = lista.closest('.vb-prod-lista-wrap');
			if (wrap && wrap.__vbListaCarousel) {
				wrap.__vbListaCarousel.refresh(true);
			}
		});

		updateProdCount(totalMatch);
	}

	function currentListaLayout(el) {
		var root = el && el.closest ? (el.closest('.vb-prod-lista-wrap') || el) : el;
		if (!root || !root.getAttribute) {
			return 'grid';
		}
		var d = root.getAttribute('data-vb-layout-desktop') || 'grid';
		var t = root.getAttribute('data-vb-layout-tablet') || d;
		var m = root.getAttribute('data-vb-layout-mobile') || d;
		if (window.matchMedia('(max-width: 767px)').matches) {
			return m;
		}
		if (window.matchMedia('(max-width: 1024px)').matches) {
			return t;
		}
		return d;
	}

	function applyPaginacao(lista, matchItems, resetPage) {
		var perPageDesktop = parseInt(lista.getAttribute('data-vb-paginacao'), 10);
		var perPageTablet = parseInt(lista.getAttribute('data-vb-paginacao-tablet'), 10);
		var perPageMobile = parseInt(lista.getAttribute('data-vb-paginacao-mobile'), 10);
		var perPage = perPageDesktop;
		if (window.matchMedia('(max-width: 767px)').matches) {
			perPage = !isNaN(perPageMobile) && perPageMobile > 0 ? perPageMobile : perPageDesktop;
		} else if (window.matchMedia('(max-width: 1024px)').matches) {
			perPage = !isNaN(perPageTablet) && perPageTablet > 0 ? perPageTablet : perPageDesktop;
		}
		if (isNaN(perPage) || perPage < 1) {
			perPage = perPageDesktop;
		}

		var wrap = lista.closest('.vb-prod-lista-wrap');
		var nav = wrap ? wrap.querySelector('[data-vb-paginacao-nav]') : null;
		var useCarousel = currentListaLayout(lista) === 'carrossel';

		if (useCarousel) {
			lista.querySelectorAll('.vb-prod-lista__item').forEach(function (item) {
				item.classList.toggle('is-hidden', item.classList.contains('is-filtered-out'));
			});
			if (nav) {
				nav.hidden = true;
			}
			return;
		}

		if (!perPage || perPage < 1 || !nav) {
			lista.querySelectorAll('.vb-prod-lista__item').forEach(function (item) {
				item.classList.toggle('is-hidden', item.classList.contains('is-filtered-out'));
			});
			return;
		}

		var page = parseInt(lista.getAttribute('data-vb-page'), 10) || 1;
		if (resetPage) {
			page = 1;
		}
		var total = matchItems.length;
		var pages = Math.max(1, Math.ceil(total / perPage));
		if (page > pages) {
			page = pages;
		}
		lista.setAttribute('data-vb-page', String(page));

		var start = (page - 1) * perPage;
		var end = start + perPage;
		lista.querySelectorAll('.vb-prod-lista__item').forEach(function (item) {
			var idx = matchItems.indexOf(item);
			var onPage = idx !== -1 && idx >= start && idx < end;
			item.classList.toggle('is-hidden', !onPage);
		});

		nav.innerHTML = '';
		if (pages <= 1) {
			nav.hidden = true;
			return;
		}
		nav.hidden = false;

		function addBtn(label, targetPage, disabled, active) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'vb-prod-paginacao__btn' + (active ? ' is-active' : '');
			btn.textContent = label;
			btn.disabled = !!disabled;
			if (!disabled && !active) {
				btn.addEventListener('click', function () {
					setListaLoading(true);
					clearTimeout(window.__vbProdPagLoadT);
					window.__vbProdPagLoadT = setTimeout(function () {
						lista.setAttribute('data-vb-page', String(targetPage));
						applyPaginacao(lista, matchItems, false);
						window.setTimeout(function () {
							setListaLoading(false);
							var target =
								(wrap && wrap.querySelector('.vb-prod-lista__item:not(.is-hidden) .vb-prod-card')) ||
								(wrap && wrap.querySelector('.vb-prod-lista__item:not(.is-hidden)')) ||
								wrap ||
								lista;
							if (target && target.scrollIntoView) {
								target.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'nearest' });
							}
						}, 280);
					}, 140);
				});
			}
			nav.appendChild(btn);
		}

		addBtn('‹', page - 1, page <= 1, false);
		for (var i = 1; i <= pages; i++) {
			addBtn(String(i), i, false, i === page);
		}
		addBtn('›', page + 1, page >= pages, false);
	}

	function setExpandOpen(root, which) {
		root.querySelectorAll('.vb-prod-expand').forEach(function (box) {
			var key = box.getAttribute('data-expand');
			var open = which && key === which;
			var trigger = box.querySelector('[data-vb-expand]');
			var panel = box.querySelector('.vb-prod-expand__panel');
			box.classList.toggle('is-open', open);
			if (trigger) {
				trigger.classList.toggle('is-active', open);
				trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
			}
			if (panel) {
				if (open) {
					panel.removeAttribute('hidden');
				} else {
					panel.setAttribute('hidden', '');
				}
			}
		});
	}

	function syncAllActive(root) {
		var hasCat = !!root.querySelector('.vb-prod-cats--categorias .vb-prod-tab.is-active[data-cat]');
		var hasMarca = !!root.querySelector('.vb-prod-cats--marcas .vb-prod-tab.is-active[data-marca]');
		var allBtn = root.querySelector('[data-all="1"]');
		if (allBtn) {
			allBtn.classList.toggle('is-active', !hasCat && !hasMarca);
		}
	}

	function initFiltros(root) {
		if (root.getAttribute('data-vb-prod-filtros') !== 'filter') {
			return;
		}

		var menuBtn = root.querySelector('[data-vb-filtros-menu]');
		var clearBtn = root.querySelector('[data-vb-filtros-clear]');
		var isMobile = function () {
			return window.matchMedia('(max-width: 767px)').matches;
		};
		var wasMobile = isMobile();

		function setMenuOpen(open) {
			root.classList.toggle('is-menu-open', open);
			if (menuBtn) {
				menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
				menuBtn.setAttribute(
					'aria-label',
					open ? 'Fechar filtros' : 'Abrir filtros'
				);
			}
		}

		function hasActiveFilter() {
			return !!(
				root.querySelector('.vb-prod-cats--categorias .vb-prod-tab.is-active[data-cat], .vb-prod-cats--categorias .vb-prod-cats__item.is-active[data-cat]') ||
				root.querySelector('.vb-prod-cats--marcas .vb-prod-tab.is-active[data-marca], .vb-prod-cats--marcas .vb-prod-cats__item.is-active[data-marca]')
			);
		}

		function syncClearLink() {
			if (!clearBtn) {
				return;
			}
			var show = isMobile() && hasActiveFilter();
			if (show) {
				clearBtn.removeAttribute('hidden');
			} else {
				clearBtn.setAttribute('hidden', '');
			}
		}

		function resetToAll() {
			var allBtn = root.querySelector('[data-all="1"]');
			root.querySelectorAll('.vb-prod-tab.is-active, .vb-prod-cats__item.is-active').forEach(function (el) {
				el.classList.remove('is-active');
			});
			if (allBtn) {
				allBtn.classList.add('is-active');
			}
			applyListaFiltersAnimated();
			syncClearLink();
			setMenuOpen(false);
		}

		function scrollProductsIntoView() {
			var wrap =
				document.querySelector('.vb-prod-lista-wrap--carousel') ||
				document.querySelector('.vb-prod-lista-wrap') ||
				document.querySelector('[data-vb-prod-lista]');
			if (!wrap) {
				return;
			}
			window.setTimeout(function () {
				var card =
					wrap.querySelector('.vb-prod-lista__item:not(.is-filtered-out):not(.is-hidden) .vb-prod-card') ||
					wrap.querySelector('.vb-prod-lista__item:not(.is-filtered-out):not(.is-hidden)') ||
					wrap;
				if (card && card.scrollIntoView) {
					card.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'nearest' });
				}
			}, 520);
		}

		// No mobile: sempre tudo encolhido (ignora "abrir por padrão").
		// No desktop: aplica data-open-default.
		if ( wasMobile ) {
			setExpandOpen(root, null);
		} else {
			var def = root.getAttribute('data-open-default') || 'nenhum';
			if (def === 'categorias' || def === 'marcas') {
				setExpandOpen(root, def);
			}
		}

		if (menuBtn) {
			menuBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				setMenuOpen(!root.classList.contains('is-menu-open'));
			});
		}

		if (clearBtn) {
			clearBtn.addEventListener('click', function () {
				resetToAll();
			});
		}

		document.addEventListener('pointerdown', function (e) {
			if (!root.classList.contains('is-menu-open')) {
				return;
			}
			if (!root.contains(e.target)) {
				setMenuOpen(false);
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && root.classList.contains('is-menu-open')) {
				setMenuOpen(false);
				if (menuBtn) {
					menuBtn.focus();
				}
			}
		});

		window.addEventListener('resize', function () {
			var nowMobile = isMobile();
			if (!nowMobile) {
				setMenuOpen(false);
				if (wasMobile) {
					var def = root.getAttribute('data-open-default') || 'nenhum';
					setExpandOpen(root, (def === 'categorias' || def === 'marcas') ? def : null);
				}
			} else if (!wasMobile && nowMobile) {
				setExpandOpen(root, null);
				setMenuOpen(false);
			}
			wasMobile = nowMobile;
			syncClearLink();
		});

		root.querySelectorAll('[data-vb-expand]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var key = btn.getAttribute('data-vb-expand');
				var box = btn.closest('.vb-prod-expand');
				var already = box && box.classList.contains('is-open');
				setExpandOpen(root, already ? null : key);
			});
		});

		root.querySelectorAll('[data-all="1"]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				root.querySelectorAll('.vb-prod-tab.is-active, .vb-prod-cats__item.is-active').forEach(function (el) {
					el.classList.remove('is-active');
				});
				btn.classList.add('is-active');
				applyListaFiltersAnimated();
				syncClearLink();
				if (isMobile()) {
					setMenuOpen(false);
				}
			});
		});

		root.querySelectorAll('.vb-prod-cats--categorias [data-cat]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				root.querySelectorAll('.vb-prod-cats--categorias [data-cat]').forEach(function (b) {
					b.classList.toggle('is-active', b === btn);
				});
				syncAllActive(root);
				applyListaFiltersAnimated();
				syncClearLink();
				if (isMobile()) {
					setMenuOpen(false);
				}
				scrollProductsIntoView();
			});
		});

		root.querySelectorAll('.vb-prod-cats--marcas [data-marca]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				root.querySelectorAll('.vb-prod-cats--marcas [data-marca]').forEach(function (b) {
					b.classList.toggle('is-active', b === btn);
				});
				syncAllActive(root);
				applyListaFiltersAnimated();
				syncClearLink();
				if (isMobile()) {
					setMenuOpen(false);
				}
				scrollProductsIntoView();
			});
		});

		syncClearLink();
	}

	function initCats(nav) {
		if (nav.closest('[data-vb-prod-filtros]')) {
			return;
		}
		if (nav.getAttribute('data-vb-prod-cats') !== 'filter') {
			return;
		}
		var buttons = nav.querySelectorAll('[data-cat]');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				buttons.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
				applyListaFiltersAnimated();
			});
		});
	}

	function initBusca(wrap) {
		var input = wrap.querySelector('.vb-prod-busca__input');
		var toggle = wrap.querySelector('.vb-prod-busca__toggle');
		if (!input) {
			return;
		}
		var live = wrap.getAttribute('data-live') !== '0';
		var timer = null;

		function setOpen(open) {
			wrap.classList.toggle('is-open', open);
			if (toggle) {
				toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			}
			if (open) {
				setTimeout(function () { input.focus(); }, 80);
			}
		}

		function syncQuery() {
			wrap.classList.toggle('has-query', !!(input.value || '').trim());
		}

		function run() {
			syncQuery();
			applyListaFiltersAnimated();
		}

		if (toggle) {
			toggle.addEventListener('click', function (e) {
				e.stopPropagation();
				setOpen(!wrap.classList.contains('is-open'));
			});
		}

		document.addEventListener('pointerdown', function (e) {
			if (!wrap.classList.contains('is-open')) {
				return;
			}
			if (!wrap.contains(e.target)) {
				setOpen(false);
			}
		});

		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && wrap.classList.contains('is-open')) {
				setOpen(false);
				if (toggle) {
					toggle.focus();
				}
			}
		});

		if (live) {
			input.addEventListener('input', function () {
				clearTimeout(timer);
				timer = setTimeout(run, 180);
			});
		}
		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				run();
			}
			if (e.key === 'Escape') {
				e.preventDefault();
				setOpen(false);
				if (toggle) {
					toggle.focus();
				}
			}
		});
		syncQuery();
		if (input.value) {
			setOpen(true);
			run();
		}
	}

	function initListaCarousel(wrap) {
		var track = wrap.querySelector('[data-vb-lista-carousel]');
		if (!track) {
			return;
		}
		var viewport = wrap.querySelector('.vb-prod-lista-viewport') || track.parentElement;
		var prev = wrap.querySelector('[data-vb-carousel-prev]');
		var next = wrap.querySelector('[data-vb-carousel-next]');
		var dotsEl = wrap.querySelector('[data-vb-carousel-dots]');
		var index = 0;

		function carouselMode() {
			return currentListaLayout(wrap) === 'carrossel' || currentListaLayout(track) === 'carrossel';
		}

		function visibleItems() {
			return Array.prototype.slice.call(
				track.querySelectorAll('.vb-prod-lista__item:not(.is-filtered-out):not(.is-hidden)')
			);
		}

		function gap() {
			var g = parseFloat(window.getComputedStyle(track).gap);
			return isNaN(g) ? 16 : g;
		}

		function perView() {
			var items = visibleItems();
			if (!items.length || !viewport) {
				return 1;
			}
			var w = viewport.clientWidth;
			var iw = items[0].getBoundingClientRect().width;
			if (iw < 1) {
				return 1;
			}
			return Math.max(1, Math.floor((w + gap()) / (iw + gap())));
		}

		function maxIndex() {
			var items = visibleItems();
			return Math.max(0, items.length - perView());
		}

		function pageCount() {
			return maxIndex() + 1;
		}

		function renderDots() {
			if (!dotsEl) {
				return;
			}
			if (!carouselMode()) {
				dotsEl.innerHTML = '';
				return;
			}
			var pages = pageCount();
			var items = visibleItems();
			if (items.length <= perView() || pages <= 1) {
				dotsEl.innerHTML = '';
				return;
			}

			var existing = dotsEl.querySelectorAll('.vb-prod-carousel-dots__dot');
			if (existing.length === pages) {
				existing.forEach(function (el, i) {
					var on = i === index;
					el.classList.toggle('is-active', on);
					el.setAttribute('aria-current', on ? 'true' : 'false');
				});
				if (existing[index] && existing[index].scrollIntoView) {
					existing[index].scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
				}
				return;
			}

			dotsEl.innerHTML = '';
			for (var i = 0; i < pages; i++) {
				(function (page) {
					var btn = document.createElement('button');
					btn.type = 'button';
					btn.className = 'vb-prod-carousel-dots__dot' + (page === index ? ' is-active' : '');
					btn.setAttribute('aria-label', 'Slide ' + (page + 1));
					btn.setAttribute('aria-current', page === index ? 'true' : 'false');
					btn.addEventListener('click', function () {
						go(page);
					});
					dotsEl.appendChild(btn);
				})(i);
			}
		}

		function go(to) {
			if (!carouselMode()) {
				index = 0;
				track.style.transform = '';
				if (dotsEl) {
					dotsEl.innerHTML = '';
				}
				return;
			}

			var pages = pageCount();
			var items = visibleItems();
			if (!items.length || pages <= 0) {
				index = 0;
				track.style.transform = 'translate3d(0,0,0)';
				renderDots();
				if (prev) {
					prev.disabled = true;
				}
				if (next) {
					next.disabled = true;
				}
				return;
			}

			// Loop infinito: após o último volta ao primeiro (e vice-versa).
			index = ((to % pages) + pages) % pages;

			var step = items[0].getBoundingClientRect().width + gap();
			track.style.transform = 'translate3d(' + (-index * step) + 'px,0,0)';
			renderDots();
			if (prev) {
				prev.disabled = false;
			}
			if (next) {
				next.disabled = false;
			}
		}

		function refresh(reset) {
			if (reset) {
				index = 0;
			}
			requestAnimationFrame(function () {
				go(index);
			});
		}

		if (prev) {
			prev.addEventListener('click', function () {
				go(index - 1);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				go(index + 1);
			});
		}

		var touchX = null;
		track.addEventListener(
			'touchstart',
			function (e) {
				touchX = e.touches[0].clientX;
			},
			{ passive: true }
		);
		track.addEventListener(
			'touchend',
			function (e) {
				if (touchX === null) {
					return;
				}
				var dx = e.changedTouches[0].clientX - touchX;
				touchX = null;
				if (Math.abs(dx) < 40) {
					return;
				}
				go(dx < 0 ? index + 1 : index - 1);
			},
			{ passive: true }
		);

		wrap.__vbListaCarousel = {
			refresh: refresh,
			go: go
		};
		refresh(true);
	}

	function initScrollNav() {
		document.querySelectorAll('[data-vb-scroll-target], .vb-prod-rel__nav').forEach(function (btn) {
			if (btn.hasAttribute('data-vb-carousel-prev') || btn.hasAttribute('data-vb-carousel-next')) {
				return;
			}
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-vb-scroll-target') || btn.getAttribute('data-target');
				var el = id ? document.getElementById(id) : null;
				if (!el) {
					return;
				}
				var delta = parseInt(btn.getAttribute('data-vb-scroll'), 10);
				if (isNaN(delta)) {
					delta = btn.classList.contains('vb-prod-nav--next') || btn.classList.contains('vb-prod-rel__nav--next') ? 300 : -300;
				}
				el.scrollBy({ left: delta, behavior: 'smooth' });
			});
		});
	}

	function boot() {
		document.querySelectorAll('[data-vb-carrossel]').forEach(initCarrossel);
		document.querySelectorAll('[data-vb-prod-filtros]').forEach(initFiltros);
		document.querySelectorAll('[data-vb-prod-cats]').forEach(initCats);
		document.querySelectorAll('[data-vb-prod-busca]').forEach(initBusca);
		document.querySelectorAll('.vb-prod-lista-wrap--carousel, .vb-prod-lista-wrap--mobile-carousel').forEach(initListaCarousel);
		initScrollNav();
		document.querySelectorAll('[data-vb-paginacao]').forEach(function (lista) {
			if (!lista.hasAttribute('data-vb-prod-lista')) {
				var items = Array.prototype.slice.call(lista.querySelectorAll('.vb-prod-lista__item'));
				applyPaginacao(lista, items, true);
			}
		});
		if (document.querySelector('[data-vb-prod-lista]')) {
			applyListaFilters();
		}
		window.addEventListener('resize', function () {
			clearTimeout(window.__vbProdResizeT);
			window.__vbProdResizeT = setTimeout(function () {
				document.querySelectorAll('[data-vb-paginacao]').forEach(function (lista) {
					var items = Array.prototype.slice.call(
						lista.querySelectorAll('.vb-prod-lista__item:not(.is-filtered-out)')
					);
					applyPaginacao(lista, items, false);
				});
				document.querySelectorAll('.vb-prod-lista-wrap--carousel, .vb-prod-lista-wrap--mobile-carousel').forEach(function (wrap) {
					if (wrap.__vbListaCarousel) {
						wrap.__vbListaCarousel.refresh(false);
					}
				});
			}, 150);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
