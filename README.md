# Valle Branco — Produtos

Catálogo leve (sem WooCommerce) para o site WordPress Valle Branco.

## Recursos

- CPT `vb_produto` (mesmo do Onde Encontrar / SAP)
- Taxonomias: categoria e marca
- Painel: capa, galeria, descrição, SKU, pesos, tabela nutricional editável
- Widgets Elementor + Theme Builder (Single / Archive)
- CTAs configuráveis: revendedor, onde encontrar, ver mais

## Instalação

1. Ative o plugin em **Plugins**
2. Vá em **Produtos → Configurações** e ajuste as URLs dos botões
3. Cadastre categorias, marcas e produtos
4. No Elementor Pro → Theme Builder, crie template **Single → Produtos**

## Widgets Elementor

Categoria **Valle Branco Produtos**:

| Widget | Uso |
|--------|-----|
| Produto — Imagem | Capa |
| Produto — Carrossel | Capa + galeria |
| Produto — Título | Título |
| Produto — Descrição | Conteúdo / excerpt |
| Produto — Botão | Revendedor / Onde encontrar / Ver mais |
| Produto — Relacionados | Mesma categoria |
| Produtos — Lista / Grade | Página de todos |
| Produtos — Categorias | Filtro ou links |
| Produto — Tabela nutricional | Dados do painel |
| Produto — Marca / Categoria | Chips |

## Compatibilidade

Com **Valle Branco — Onde Encontrar** ativo:

- Este plugin passa a ser o dono do CPT `vb_produto`
- Meta `_vb_sku`, `_vb_marca`, `_vb_categoria`, `_vb_pesos` continuam alinhadas ao mapa/n8n
- Singles em `/produto/slug` — a página Elementor `/produtos` não é sobrescrita (sem archive do CPT)
