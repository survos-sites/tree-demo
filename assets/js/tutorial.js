// with export default, use import ExclamFunction from '/.tutorial.js';
export default function(exCount) {
    return '!'.repeat(exCount);
}

// with module.exports, use const GetExMessage = require('.tutorial.js')
// old way, from Node, the official way is with export default, not module.exports / require
// use import for css, too
//  import .. from returns a value
/*
module.exports = function (exCount) {
    return '!'.repeat(exCount);
}
 */