(function ($) {
	'use strict';

	function syncGalleryInput($wrap) {
		var ids = [];
		$wrap.find('.vb-prod-galeria__list li').each(function () {
			ids.push($(this).data('id'));
		});
		$wrap.find('#vb_prod_galeria').val(ids.join(','));
	}

	function initGallery() {
		var $wrap = $('.vb-prod-galeria');
		if (!$wrap.length) {
			return;
		}

		var frame;

		$('#vb_prod_galeria_add').on('click', function (e) {
			e.preventDefault();
			if (frame) {
				frame.open();
				return;
			}
			frame = wp.media({
				title: $wrap.data('frame-title') || 'Selecionar imagens',
				button: { text: 'Usar imagens' },
				multiple: true,
				library: { type: 'image' }
			});
			frame.on('select', function () {
				var selection = frame.state().get('selection');
				selection.each(function (att) {
					var id = att.get('id');
					if ($wrap.find('li[data-id="' + id + '"]').length) {
						return;
					}
					var url = att.get('sizes') && att.get('sizes').thumbnail
						? att.get('sizes').thumbnail.url
						: att.get('url');
					$wrap.find('.vb-prod-galeria__list').append(
						'<li data-id="' + id + '">' +
						'<img src="' + url + '" alt="" />' +
						'<button type="button" class="vb-prod-galeria__remove" aria-label="Remover">&times;</button>' +
						'</li>'
					);
				});
				syncGalleryInput($wrap);
			});
			frame.open();
		});

		$wrap.on('click', '.vb-prod-galeria__remove', function (e) {
			e.preventDefault();
			$(this).closest('li').remove();
			syncGalleryInput($wrap);
		});

		$('#vb_prod_galeria_clear').on('click', function (e) {
			e.preventDefault();
			$wrap.find('.vb-prod-galeria__list').empty();
			syncGalleryInput($wrap);
		});
	}

	function uid() {
		return 'r' + Date.now() + Math.floor(Math.random() * 1000);
	}

	function initTabelas() {
		var $box = $('.vb-prod-tabelas');
		if (!$box.length) {
			return;
		}

		var msgTable = $box.data('confirm-table') || 'Excluir esta tabela?';
		var msgRow = $box.data('confirm-row') || 'Excluir esta linha?';
		var phTitle = $box.data('title-placeholder') || 'Título da tabela';
		var phCampo = $box.data('campo-placeholder') || 'Campo';
		var phValor = $box.data('valor-placeholder') || 'Valor';

		function nextIndex() {
			var max = -1;
			$box.find('.vb-prod-tabela-card').each(function () {
				var i = parseInt($(this).attr('data-index'), 10);
				if (!isNaN(i) && i > max) {
					max = i;
				}
			});
			return max + 1;
		}

		function rowHtml(ti, ri) {
			var prefix = 'vb_prod_nutricao[tabelas][' + ti + '][linhas][' + ri + ']';
			return (
				'<tr>' +
				'<td><input type="text" name="' + prefix + '[campo]" value="" placeholder="' + phCampo + '" /></td>' +
				'<td><input type="text" name="' + prefix + '[valor]" value="" placeholder="' + phValor + '" /></td>' +
				'<td class="vb-prod-tabela-card__actions">' +
				'<button type="button" class="button-link-delete vb-prod-tabela-card__rm-row" title="Excluir linha">&times;</button>' +
				'</td>' +
				'</tr>'
			);
		}

		function cardHtml(ti) {
			var prefix = 'vb_prod_nutricao[tabelas][' + ti + ']';
			return (
				'<div class="vb-prod-tabela-card" data-index="' + ti + '">' +
				'<div class="vb-prod-tabela-card__head">' +
				'<div class="vb-prod-tabela-card__title-wrap">' +
				'<input type="text" class="vb-prod-tabela-card__titulo" name="' + prefix + '[titulo]" value="" placeholder="' + phTitle + '" />' +
				'<label class="vb-prod-tabela-card__estilo-label"><span>Estilo</span>' +
				'<select name="' + prefix + '[estilo]">' +
				'<option value="azul" selected>Azul</option>' +
				'<option value="ouro">Ouro</option>' +
				'</select></label>' +
				'</div>' +
				'<button type="button" class="button-link-delete vb-prod-tabela-card__rm">Excluir tabela</button>' +
				'</div>' +
				'<table class="widefat striped vb-prod-tabela-card__table">' +
				'<thead><tr><th>Campo</th><th>Valor</th><th class="vb-prod-tabela-card__actions"></th></tr></thead>' +
				'<tbody>' + rowHtml(ti, uid()) + '</tbody>' +
				'</table>' +
				'<p class="vb-prod-tabela-card__footer">' +
				'<button type="button" class="button vb-prod-tabela-card__add-row">Adicionar linha</button>' +
				'</p>' +
				'</div>'
			);
		}

		$('#vb_prod_tabela_add').on('click', function (e) {
			e.preventDefault();
			$box.find('.vb-prod-tabelas__list').append(cardHtml(nextIndex()));
		});

		$box.on('click', '.vb-prod-tabela-card__add-row', function (e) {
			e.preventDefault();
			var $card = $(this).closest('.vb-prod-tabela-card');
			var ti = $card.attr('data-index');
			$card.find('tbody').append(rowHtml(ti, uid()));
		});

		$box.on('click', '.vb-prod-tabela-card__rm-row', function (e) {
			e.preventDefault();
			if (!window.confirm(msgRow)) {
				return;
			}
			var $tbody = $(this).closest('tbody');
			var $rows = $tbody.find('tr');
			if ($rows.length <= 1) {
				$rows.find('input').val('');
				return;
			}
			$(this).closest('tr').remove();
		});

		$box.on('click', '.vb-prod-tabela-card__rm', function (e) {
			e.preventDefault();
			if (!window.confirm(msgTable)) {
				return;
			}
			var $list = $box.find('.vb-prod-tabelas__list');
			$(this).closest('.vb-prod-tabela-card').remove();
			if (!$list.find('.vb-prod-tabela-card').length) {
				$list.append(cardHtml(0));
			}
		});
	}

	$(function () {
		initGallery();
		initTabelas();
	});
})(jQuery);
