let _env = null;

function _setEnv() {
    let zIndex = getComputedStyle(document.body).zIndex;
    if (zIndex == "1") {
        _env = "simple";
    }
    else if (zIndex == "2") {
        _env = "tablet";
    }
    else if (zIndex == "3") {
        _env = "desktop";
    }
}

_setEnv();

window.addEventListener("resize", _setEnv);

export default {
    /**
     * @returns {string}
     */
    getEnv: function () {
        return _env;
    },
    /**
     * @returns {boolean}
     */
    isSimple: function () {
        return _env === "simple";
    },
    /**
     * @returns {boolean}
     */
    isTablet: function () {
        return _env === "tablet";
    },
    /**
     * @returns {boolean}
     */
    isDesktop: function () {
        return _env === "desktop";
    }
};