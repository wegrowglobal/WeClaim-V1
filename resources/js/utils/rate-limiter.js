export class RateLimiter {
    constructor(maxRequests = 10, timeWindow = 1000) {
        this.maxRequests = maxRequests;
        this.timeWindow = timeWindow;
        this.tokens = maxRequests;
        this.lastRefill = Date.now();
    }

    async acquire() {
        await this.refillTokens();
        if (this.tokens <= 0) {
            const waitTime = Math.ceil(this.timeWindow / this.maxRequests);
            await new Promise(resolve => setTimeout(resolve, waitTime));
            await this.refillTokens();
        }
        this.tokens--;
        return true;
    }

    async refillTokens() {
        const now = Date.now();
        const timePassed = now - this.lastRefill;
        const refillAmount = Math.floor(timePassed / this.timeWindow) * this.maxRequests;
        
        if (refillAmount > 0) {
            this.tokens = Math.min(this.maxRequests, this.tokens + refillAmount);
            this.lastRefill = now;
        }
    }
} 