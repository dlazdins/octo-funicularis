/**
 * Helper methods.
 * Usage:
 * import {throttle} from "helpers/utils";
 * import {throttle, debounce} from "helpers/utils";
 *
 * Unused methods will be dropped during build process.
 */

export let throttle = (fn: Function, _threshold: number, scope): Function => {
    let threshold = _threshold || 250;
    let last,
        deferTimer;
    return function () {
        let context = scope || this;
        let now = +new Date(),
            args = arguments;
        if (last && now < last + threshold) {
            // hold on to it
            clearTimeout(deferTimer);
            deferTimer = setTimeout(function () {
                last = now;
                fn.apply(context, args);
            }, threshold);
        } else {
            last = now;
            fn.apply(context, args);
        }
    };
};

// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.

export let debounce = (func: Function, wait: number, immediate: boolean): Function => {
    let timeout;
    return function () {
        let context = this, args = arguments;
        let later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        let callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
};

/**
 * Get query param from URL by key
 *
 * @param key
 * @returns string
 */
export let getQueryParam = (key: string): string => {
    let result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === key) result = decodeURIComponent(tmp[1]);
        });
    return result;
};

/**
 * Update query param
 *
 * @param {string} uri
 * @param {string} key
 * @param {string} value
 * @return {string}
 */
export function updateQueryParam(uri: string, key: string, value: string | number | string[]) {
    let re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    let separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
        return uri + separator + key + "=" + value;
    }
}

/**
 * Vanilla implementation of lodash's _.find( array, function(){})
 *
 * Usage:
 * find([{foo: "bar", boo: "far"}], function(n) {
 *  return n.foo === "bar";
 * });
 *
 * @param array
 * @param predicate
 * @returns {}
 */
export function find(array: Array<any>, predicate: Function) {

    if (array == null) {
        throw new TypeError('"array" is null or not defined');
    }

    let len = array.length >>> 0;

    if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
    }

    let thisArg = arguments[1];
    let k = 0;

    while (k < len) {
        let kValue = array[k];
        if (predicate.call(thisArg, kValue, k, array)) {
            return kValue;
        }
        k++;
    }

    return undefined;
}


/**
 * Merge objects
 *
 * @param obj
 * @param additional
 * @returns {Object}
 */
export function merge(obj: Object, additional: Object): Object {
    let source, prop;
    for (let i = 1, length = arguments.length; i < length; i++) {
        source = arguments[i];
        for (prop in source) {
            obj[prop] = source[prop];
        }
    }
    return obj;
}
