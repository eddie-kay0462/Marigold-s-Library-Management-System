/**
 * Marigold Library Management System
 * Custom Notification System
 */

// Create notification container if it doesn't exist
function createNotificationContainer() {
    let container = document.querySelector(".notification-container")
    if (!container) {
      container = document.createElement("div")
      container.className = "notification-container"
      document.body.appendChild(container)
    }
    return container
  }
  
  // Show success notification
  function showSuccessMessage(message) {
    const container = createNotificationContainer()
    const notification = document.createElement("div")
    notification.className = "notification notification-success"
    notification.innerHTML = `
      <div class="notification-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <div class="notification-content">
        <div class="notification-title">Success</div>
        <div class="notification-message">${message}</div>
      </div>
      <button class="notification-close">
        <i class="fas fa-times"></i>
      </button>
    `
  
    container.appendChild(notification)
    setupNotificationEvents(notification)
  }
  
  // Show error notification
  function showErrorMessage(message) {
    const container = createNotificationContainer()
    const notification = document.createElement("div")
    notification.className = "notification notification-error"
    notification.innerHTML = `
      <div class="notification-icon">
        <i class="fas fa-exclamation-circle"></i>
      </div>
      <div class="notification-content">
        <div class="notification-title">Error</div>
        <div class="notification-message">${message}</div>
      </div>
      <button class="notification-close">
        <i class="fas fa-times"></i>
      </button>
    `
  
    container.appendChild(notification)
    setupNotificationEvents(notification)
  }
  
  // Show info notification
  function showInfoMessage(message) {
    const container = createNotificationContainer()
    const notification = document.createElement("div")
    notification.className = "notification notification-info"
    notification.innerHTML = `
      <div class="notification-icon">
        <i class="fas fa-info-circle"></i>
      </div>
      <div class="notification-content">
        <div class="notification-title">Information</div>
        <div class="notification-message">${message}</div>
      </div>
      <button class="notification-close">
        <i class="fas fa-times"></i>
      </button>
    `
  
    container.appendChild(notification)
    setupNotificationEvents(notification)
  }
  
  // Show warning notification
  function showWarningMessage(message) {
    const container = createNotificationContainer()
    const notification = document.createElement("div")
    notification.className = "notification notification-warning"
    notification.innerHTML = `
      <div class="notification-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      <div class="notification-content">
        <div class="notification-title">Warning</div>
        <div class="notification-message">${message}</div>
      </div>
      <button class="notification-close">
        <i class="fas fa-times"></i>
      </button>
    `
  
    container.appendChild(notification)
    setupNotificationEvents(notification)
  }
  
  // Setup notification events (close button, auto-dismiss, animations)
  function setupNotificationEvents(notification) {
    // Add close button event
    const closeBtn = notification.querySelector(".notification-close")
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        dismissNotification(notification)
      })
    }
  
    // Trigger entrance animation
    setTimeout(() => {
      notification.classList.add("show")
    }, 10)
  
    // Auto dismiss after 5 seconds
    setTimeout(() => {
      dismissNotification(notification)
    }, 3000)
  }
  
  // Dismiss notification with animation
  function dismissNotification(notification) {
    notification.classList.add("hide")
    notification.addEventListener("transitionend", () => {
      notification.remove()
    })
  }
  
  // Add notification styles to the document
  function addNotificationStyles() {
    if (document.getElementById("notification-styles")) return
  
    const style = document.createElement("style")
    style.id = "notification-styles"
    style.textContent = `
      .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 350px;
      }
      
      .notification {
        display: flex;
        align-items: flex-start;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 16px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
        width: 100%;
      }
      
      .notification.show {
        transform: translateX(0);
      }
      
      .notification.hide {
        transform: translateX(120%);
        opacity: 0;
        transition: transform 0.4s cubic-bezier(0.6, -0.28, 0.735, 0.045), opacity 0.3s ease;
      }
      
      .notification-icon {
        margin-right: 12px;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      .notification-content {
        flex: 1;
      }
      
      .notification-title {
        font-weight: 600;
        margin-bottom: 4px;
        font-size: 16px;
      }
      
      .notification-message {
        font-size: 14px;
        color: #666;
        line-height: 1.4;
      }
      
      .notification-close {
        background: transparent;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 14px;
        padding: 0;
        margin-left: 8px;
        transition: color 0.2s;
      }
      
      .notification-close:hover {
        color: #333;
      }
      
      /* Success notification */
      .notification-success {
        border-left: 4px solid #4CAF50;
      }
      
      .notification-success .notification-icon {
        color: #4CAF50;
      }
      
      /* Error notification */
      .notification-error {
        border-left: 4px solid #F44336;
      }
      
      .notification-error .notification-icon {
        color: #F44336;
      }
      
      /* Info notification */
      .notification-info {
        border-left: 4px solid #2196F3;
      }
      
      .notification-info .notification-icon {
        color: #2196F3;
      }
      
      /* Warning notification */
      .notification-warning {
        border-left: 4px solid #FF9800;
      }
      
      .notification-warning .notification-icon {
        color: #FF9800;
      }
      
      @media (max-width: 480px) {
        .notification-container {
          right: 10px;
          left: 10px;
          max-width: calc(100% - 20px);
        }
      }
    `
  
    document.head.appendChild(style)
  }
  
  // Initialize notification system
  document.addEventListener("DOMContentLoaded", () => {
    addNotificationStyles()
  })
  
  // Override the default alert function to use our custom notifications
  window.alert = (message) => {
    showInfoMessage(message)
  }
  
  // Make notification functions globally available
  window.showSuccessMessage = showSuccessMessage
  window.showErrorMessage = showErrorMessage
  window.showInfoMessage = showInfoMessage
  window.showWarningMessage = showWarningMessage
  
  // Initialize immediately if document is already loaded
  if (document.readyState === "complete" || document.readyState === "interactive") {
    addNotificationStyles()
  }
  