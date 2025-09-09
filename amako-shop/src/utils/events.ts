type EventCallback = (...args: any[]) => void;

class EventEmitter {
  private events: { [key: string]: EventCallback[] } = {};

  on(event: string, callback: EventCallback): void {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    this.events[event].push(callback);
  }

  off(event: string, callback: EventCallback): void {
    if (!this.events[event]) return;
    
    const index = this.events[event].indexOf(callback);
    if (index > -1) {
      this.events[event].splice(index, 1);
    }
  }

  emit(event: string, ...args: any[]): void {
    if (!this.events[event]) return;
    
    this.events[event].forEach(callback => {
      try {
        callback(...args);
      } catch (error) {
        console.error(`Error in event ${event} callback:`, error);
      }
    });
  }

  removeAllListeners(event?: string): void {
    if (event) {
      delete this.events[event];
    } else {
      this.events = {};
    }
  }
}

// Create a global event emitter instance
export const eventEmitter = new EventEmitter();

// Common event names
export const AUTH_EVENTS = {
  UNAUTHORIZED: 'auth:unauthorized',
  TOKEN_EXPIRED: 'auth:token_expired',
  LOGIN_SUCCESS: 'auth:login_success',
  LOGOUT: 'auth:logout',
} as const;

// Convenience functions for common events
export const emitUnauthorized = () => eventEmitter.emit(AUTH_EVENTS.UNAUTHORIZED);
export const emitTokenExpired = () => eventEmitter.emit(AUTH_EVENTS.TOKEN_EXPIRED);
export const emitLoginSuccess = (user: any) => eventEmitter.emit(AUTH_EVENTS.LOGIN_SUCCESS, user);
export const emitLogout = () => eventEmitter.emit(AUTH_EVENTS.LOGOUT);

export default eventEmitter;
