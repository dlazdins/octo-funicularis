/**
 * A special exported onStart init method is needed only when a module needs to be executed dynamically,
 * such as after an ajax call.
 *
 * Otherwise, write your code as normal
 */


let onStart = () => {
    console.log("Language page init");
};

export default {onStart};