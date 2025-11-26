import { registerAutoLoad } from "../helpers/autoload";

class Dashboard {
    constructor() {
        this.init();
    }

    init() {
        console.log('Dashboard initialized');
    }
}

registerAutoLoad('dashboard', Dashboard);