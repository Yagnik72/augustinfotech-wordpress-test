const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        index: [
            './blocks/top-blogs/index.js',
            './blocks/top-blogs/style-index.css'
        ]
    },
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: '[name].js'
    }
}; 