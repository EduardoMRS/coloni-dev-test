<template>
  <div class="context-menu-container">
    <div @contextmenu.prevent="handleContextMenu" style="display: contents;">
      <slot></slot>
    </div>
    
    <transition to="body">
      <div
        v-if="isVisible"
        ref="contextMenu"
        class="context-menu"
        :style="menuStyle"
        @click.stop
        @contextmenu.prevent
      >
        <ContextMenuList :items="menuItems" @item-click="handleItemClick" />
      </div>
    </transition>
    
    <transition to="body">
      <div
        v-if="isVisible"
        class="context-menu-overlay"
        @click="close"
        @contextmenu.prevent="close"
        @scroll="close"
        @resize="close"
      ></div>
    </transition>
  </div>
</template>

<script>
import ContextMenuList from './ContextMenuList.vue'

const variants = {
  primary: {
    background: '#ffffff',
    borderColor: '#e0e0e0',
    textColor: '#333333',
    hoverBackground: '#cce5ff',
    disabledColor: '#999999',
    separatorColor: '#e0e0e0',
    shortcutColor: '#666666'
  },
  dark: {
    background: '#2d2d2d',
    borderColor: '#444444',
    textColor: '#ffffff',
    hoverBackground: '#404040',
    disabledColor: '#888888',
    separatorColor: '#444444',
    shortcutColor: '#aaaaaa'
  },
  light: {
    background: '#ffffff',
    borderColor: '#e0e0e0',
    textColor: '#333333',
    hoverBackground: '#f5f5f5',
    disabledColor: '#999999',
    separatorColor: '#e0e0e0',
    shortcutColor: '#666666'
  },
}
export default {
  name: 'ContextMenu',
  components: {
    ContextMenuList
  },
  props: {
    menuItems: {
      type: Array,
      default: () => []
    },
    zIndex: {
      type: Number,
      default: 2000
    },
    disabled: {
      type: Boolean,
      default: false
    },
    variant: {
      type: String,
      default: 'default',
      validator(value) {
        return [ 'default', 'dark', 'light', 'primary'].includes(value)
      }
    }
  },
  
  data() {
    return {
      isVisible: false,
      position: {
        x: 0,
        y: 0
      },
      menuDimensions: {
        width: 0,
        height: 0
      }
    }
  },
  
  computed: {
    menuStyle() {
      const variantStyles = variants[this.variant] || variants[window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'];
      return {
        '--context-menu-bg': variantStyles.background,
        '--context-menu-border': variantStyles.borderColor,
        '--context-menu-text': variantStyles.textColor,
        '--context-menu-hover-bg': variantStyles.hoverBackground,
        '--context-menu-disabled': variantStyles.disabledColor,
        '--context-menu-separator': variantStyles.separatorColor,
        '--context-menu-shortcut': variantStyles.shortcutColor,
        position: 'fixed',
        top: `${this.position.y}px`,
        left: `${this.position.x}px`,
        zIndex: this.zIndex,
        transform: this.getTransform()
      }
    },
  },
  
  mounted() {
    document.addEventListener('click', this.handleDocumentClick)
    document.addEventListener('keydown', this.handleKeydown)
    window.addEventListener('resize', this.close)
    window.addEventListener('scroll', this.close, true)
  },
  
  beforeDestroy() {
    document.removeEventListener('click', this.handleDocumentClick)
    document.removeEventListener('keydown', this.handleKeydown)
    window.removeEventListener('resize', this.close)
    window.removeEventListener('scroll', this.close, true)
  },
  
  methods: {
    handleContextMenu(event) {
      if (this.disabled || !this.menuItems.length) {
        return
      }
      
      event.preventDefault()
      event.stopPropagation()
      
      this.position.x = event.clientX
      this.position.y = event.clientY
      
      this.isVisible = true
      
      this.$nextTick(() => {
        this.adjustPosition()
        this.$emit('open', { event, position: this.position })
      })
    },
    
    handleItemClick(item) {
      if (item.disabled || item.children) {
        return
      }
      
      this.close()
      
      if (item.action && typeof item.action === 'function') {
        item.action(item)
      }
      
      this.$emit('item-click', item)
    },
    
    handleItemHover(item) {
      this.$emit('item-hover', item)
    },
    
    handleDocumentClick(event) {
      if (this.isVisible) {
        this.close()
      }
    },
    
    handleKeydown(event) {
      if (event.key === 'Escape' && this.isVisible) {
        this.close()
      }
    },
    
    adjustPosition() {
      if (!this.$refs.contextMenu) return
      
      const menu = this.$refs.contextMenu
      const rect = menu.getBoundingClientRect()
      const viewport = {
        width: window.innerWidth,
        height: window.innerHeight
      }
      
      this.menuDimensions.width = rect.width
      this.menuDimensions.height = rect.height
      
      if (this.position.x + rect.width > viewport.width) {
        this.position.x = viewport.width - rect.width - 10
      }
      
      if (this.position.y + rect.height > viewport.height) {
        this.position.y = viewport.height - rect.height - 10
      }
      
      this.position.x = Math.max(10, this.position.x)
      this.position.y = Math.max(10, this.position.y)
    },
    
    getTransform() {
      return 'translate(0, 0)'
    },
    
    close() {
      if (this.isVisible) {
        this.isVisible = false
        this.$emit('close')
      }
    },
    
    open(x, y) {
      if (this.disabled) return
      
      this.position.x = x || 0
      this.position.y = y || 0
      this.isVisible = true
      
      this.$nextTick(() => {
        this.adjustPosition()
      })
    }
  }
}
</script>

<style scoped>
.context-menu-container {
  position: relative;
}

.context-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 1999;
  background: transparent;
}

.context-menu {
  position: fixed;
  background: var(--context-menu-bg);
  border: 1px solid var(--context-menu-border);
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 4px 0;
  min-width: 180px;
  max-width: 300px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 14px;
  user-select: none;
  z-index: 2000;
}

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
  border-top: 1px solid var(--context-menu-separator);
  margin: 4px 8px;
  height: 1px;
}

.context-menu-link {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  color: var(--context-menu-text);
  text-decoration: none;
  transition: all 0.15s ease;
  cursor: pointer;
  border-radius: 0;
}

.context-menu-link:hover:not(.disabled) {
  background-color: var(--context-menu-hover-bg);
  color: var(--context-menu-text);
}

.context-menu-link.disabled {
  color: var(--context-menu-disabled);
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
  color: var(--context-menu-shortcut);
  flex-shrink: 0;
}

/* Dark theme support */
@media (prefers-color-scheme: dark) {
  .context-menu {
    background: var(--context-menu-bg);
    border-color: var(--context-menu-border);
    color: var(--context-menu-text);
  }
  
  .context-menu-link {
    color: var(--context-menu-text);
  }
  
  .context-menu-link:hover:not(.disabled) {
    background-color: var(--context-menu-hover-bg);
  }
  
  .context-menu-link.disabled {
    color: var(--context-menu-disabled);
  }
  
  .context-menu-separator {
    border-color: var(--context-menu-separator);
  }
  
  .context-menu-shortcut {
    color: var(--context-menu-shortcut);
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .context-menu {
    min-width: 160px;
    font-size: 16px;
  }
  
  .context-menu-link {
    padding: 12px 16px;
  }
  
  .context-menu-icon {
    width: 24px;
    height: 24px;
    font-size: 18px;
  }
}
</style>