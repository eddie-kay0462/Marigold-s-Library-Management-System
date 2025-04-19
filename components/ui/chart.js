export class Chart {
    constructor(canvas, config) {
      this.canvas = canvas
      this.config = config
    }
  
    destroy() {
      // Basic destroy method to avoid errors
      this.canvas = null
      this.config = null
    }
  }
  