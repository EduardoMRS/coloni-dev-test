<template>
  <ul class="context-menu-list">
    <li
      v-for="(item, index) in items"
      :key="index"
      :class="[
        'context-menu-item',
        {
          'context-menu-divider': item.divider,
          'context-menu-disabled': item.disabled,
          'context-menu-submenu': item.children && item.children.length > 0,
          'context-menu-submenu-open': openedSubmenu === index
        }
      ]"
    >
      <hr v-if="item.divider" class="context-menu-separator" />
      
      <a
        v-else
        href="#"
        class="context-menu-link"
        :class="{ 'disabled': item.disabled }"
        :title="item.label"
        @click.prevent="handleItemClick(item, index)"
        @mouseenter="handleSubmenuHover(item, index)"
        @mouseleave="handleSubmenuLeave"
      >
        <span v-if="item.icon && typeof item.icon !== 'string'" class="context-menu-icon">
          <component :is="item.icon" class="context-menu-icon-svg" />
        </span>
        <span class="context-menu-label">{{ item.label }}</span>
        <span v-if="item.children && item.children.length" class="context-menu-submenu-arrow">&#8250;</span>
        <span v-else-if="item.shortcut" class="context-menu-shortcut">{{ item.shortcut }}</span>
      </a>

      <!-- Submenu recursivo -->
      <transition name="context-submenu-fade">
        <div
          v-if="item.children && item.children.length && openedSubmenu === index"
          class="context-menu-submenu-container"
          :style="submenuStyle"
          @mouseenter="handleSubmenuContainerHover"
          @mouseleave="handleSubmenuLeave"
        >
          <ContextMenuList :items="item.children" @item-click="$emit('item-click', $event)" />
        </div>
      </transition>
    </li>
  </ul>
</template>

<script>
export default {
  name: 'ContextMenuList',
  props: {
    items: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      openedSubmenu: null,
      submenuHoverTimer: null
    }
  },
  computed: {
    submenuStyle() {
      return {
        position: 'absolute',
        top: '0',
        left: '100%',
        marginLeft: '4px',
        minWidth: '180px',
        maxWidth: '300px'
      }
    }
  },
  methods: {
    handleItemClick(item, index) {
      if (item.disabled) {
        return
      }

      // Se tem submenu, abre/fecha ao invés de executar ação
      if (item.children && item.children.length) {
        this.openedSubmenu = this.openedSubmenu === index ? null : index
        return
      }

      // Caso contrário, emite o click do item
      this.$emit('item-click', item)
    },

    handleSubmenuHover(item, index) {
      if (!item.children || !item.children.length) {
        return
      }

      // Limpa timer anterior se existir
      if (this.submenuHoverTimer) {
        clearTimeout(this.submenuHoverTimer)
      }

      // Abre submenu com delay pequeno para melhor UX
      this.submenuHoverTimer = setTimeout(() => {
        this.openedSubmenu = index
      }, 150)
    },

    handleSubmenuContainerHover() {
      // Mantém o submenu aberto quando hover no container
      if (this.submenuHoverTimer) {
        clearTimeout(this.submenuHoverTimer)
      }
    },

    handleSubmenuLeave() {
      // Fecha submenu com delay
      if (this.submenuHoverTimer) {
        clearTimeout(this.submenuHoverTimer)
      }

      this.submenuHoverTimer = setTimeout(() => {
        this.openedSubmenu = null
      }, 100)
    }
  },

  beforeDestroy() {
    if (this.submenuHoverTimer) {
      clearTimeout(this.submenuHoverTimer)
    }
  }
}
</script>

<style scoped>
.context-menu-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.context-menu-item {
  position: relative;
}

.context-menu-divider {
  height: 1px;
  margin: 4px 0;
}

.context-menu-separator {
  border: none;
  border-top: 1px solid var(--context-menu-separator, #e0e0e0);
  margin: 4px 8px;
  height: 1px;
}

.context-menu-link {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  color: var(--context-menu-text, #333333);
  text-decoration: none;
  transition: all 0.15s ease;
  cursor: pointer;
  border-radius: 0;
  user-select: none;
}

.context-menu-link:hover:not(.disabled) {
  background-color: var(--context-menu-hover-bg, #f5f5f5);
  color: var(--context-menu-text, #333333);
}

.context-menu-link.disabled {
  color: var(--context-menu-disabled, #999999);
  cursor: not-allowed;
  opacity: 0.6;
}

.context-menu-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  margin-right: 8px;
  font-size: 16px;
  flex-shrink: 0;
}

.context-menu-submenu-arrow {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 16px;
  height: 16px;
  margin-left: auto;
  margin-right: -4px;
  font-size: 12px;
  flex-shrink: 0;
  opacity: 0.7;
}

.context-menu-submenu:hover .context-menu-submenu-arrow {
  opacity: 1;
}

.context-menu-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.context-menu-shortcut {
  margin-left: 12px;
  font-size: 12px;
  color: var(--context-menu-shortcut, #666666);
  flex-shrink: 0;
}

.context-menu-submenu-container {
  background: var(--context-menu-bg, #ffffff);
  border: 1px solid var(--context-menu-border, #e0e0e0);
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 4px 0;
  z-index: 2001;
}

/* Animação para submenu */
.context-submenu-fade-enter-active,
.context-submenu-fade-leave-active {
  transition: opacity 0.1s ease;
}

.context-submenu-fade-enter-from,
.context-submenu-fade-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .context-menu-link {
    padding: 12px 16px;
  }

  .context-menu-icon {
    width: 24px;
    height: 24px;
    font-size: 18px;
  }

  .context-menu-submenu-container {
    min-width: 160px;
  }
}
</style>
