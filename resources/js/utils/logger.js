class Logger {
    static DEBUG = false;

    static init(debug = false) {
        this.DEBUG = debug;
    }

    static log(message, data = null) {
        if (!this.DEBUG) return;
        if (data) {
            console.log(`📝 ${message}:`, data);
        } else {
            console.log(`📝 ${message}`);
        }
    }

    static warn(message, data = null) {
        if (data) {
            console.warn(`⚠️ ${message}:`, data);
        } else {
            console.warn(`⚠️ ${message}`);
        }
    }

    static error(message, error = null) {
        if (error) {
            console.error(`❌ ${message}:`, error);
        } else {
            console.error(`❌ ${message}`);
        }
    }

    static group(label) {
        if (!this.DEBUG) return;
        console.group(`📑 ${label}`);
    }

    static groupEnd() {
        if (!this.DEBUG) return;
        console.groupEnd();
    }

    static table(data, columns = null) {
        if (!this.DEBUG) return;
        console.table(data, columns);
    }
}

export default Logger; 