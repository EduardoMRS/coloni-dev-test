# ContextMenu Component

Menu de contexto (botão direito) customizável com suporte a submenus, ícones, atalhos e temas.

## Uso Básico

```vue
<ContextMenu :menu-items="menuItems" @item-click="handleClick">
  <div>Clique com botão direito aqui</div>
</ContextMenu>
```

```js
menuItems: [
  { label: 'Visualizar', icon: 'eye', action: () => this.visualizar() },
  { divider: true },
  { label: 'Copiar', icon: 'copy', action: () => this.copiar(), shortcut: 'Ctrl+C' },
  { label: 'Desabilitado', icon: 'ban', disabled: true },
]
```

## Abertura Programática

```vue
<ContextMenu ref="menu" :menu-items="items" />

<!-- Abrir via código (ex: no evento de linha da tabela) -->
<b-table @row-contextmenu="onRowContext" />
```

```js
onRowContext(item, index, event) {
  event.preventDefault()
  this.$refs.menu.open(event.clientX, event.clientY)
}
```

## Submenus

Items com `children` exibem um submenu ao passar o mouse:

```js
menuItems: [
  {
    label: 'Exportar',
    icon: 'file-export',
    children: [
      { label: 'Excel', icon: 'file-excel', action: () => this.exportExcel() },
      { label: 'PDF', icon: 'file-pdf', action: () => this.exportPdf() },
      { divider: true },
      { label: 'Outros formatos', icon: 'ellipsis-h',
        children: [
          { label: 'CSV', action: () => this.exportCsv() },
          { label: 'JSON', action: () => this.exportJson() },
        ]
      }
    ]
  },
  { divider: true },
  { label: 'Fechar', icon: 'times', action: () => this.fechar() }
]
```

## Props

| Prop | Tipo | Default | Descrição |
|------|------|---------|-----------|
| `menuItems` | `Array` | `[]` | Itens do menu |
| `variant` | `String` | `'primary'` | Tema: `light`, `dark`, `primary`, `fiscontech`, `smartdiscover` |
| `zIndex` | `Number` | `2000` | Z-index do menu |
| `disabled` | `Boolean` | `false` | Desabilita o menu |

## Estrutura de um Item

```js
{
  label: 'Texto',           // obrigatório
  icon: 'eye',              // ícone FontAwesome (opcional)
  action: () => {},          // callback ao clicar (opcional)
  shortcut: 'Ctrl+C',       // texto do atalho (exibição) (opcional)
  disabled: false,           // desabilitado (opcional)
  divider: true,             // separador visual (opcional, ignora demais props)
  children: []               // submenu (opcional, Array de items)
}
```

## Eventos

| Evento | Payload | Descrição |
|--------|---------|-----------|
| `item-click` | `item` | Item clicado |
| `open` | `{ event, position }` | Menu aberto |
| `close` | — | Menu fechado |

## Métodos

| Método | Params | Descrição |
|--------|--------|-----------|
| `open(x, y)` | coordenadas | Abre o menu programaticamente |
| `close()` | — | Fecha o menu |