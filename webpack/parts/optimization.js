module.exports = (env) => {
  'use strict';

  /*
   * Import global/vendor dependencies.
   */
  const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
  const TerserPlugin = require('terser-webpack-plugin');

  /*
   * Setup boolean check to toggle configuration settings based on whether the environment should be optimized for production or not.
   */
  const optimizeForProduction = (typeof env !== 'undefined' && env && env.production === true);

  /*
   * Initiate optimization object.
   */
  let optimization = {};

  /*
   * Configure optimization based on environment argument.
   */
  if (optimizeForProduction) {
    optimization = {
      minimizer: [
        new TerserPlugin({
          terserOptions: {
            ecma: undefined,
            warnings: false,
            parse: {},
            compress: {
              drop_console: true // strips out console.log declarations.
            },
            mangle: {},
            module: false,
            output: null,
            toplevel: false,
            nameCache: null,
            ie8: false,
            keep_classnames: undefined,
            keep_fnames: false,
            safari10: false,
          },
        }),
        new OptimizeCSSAssetsPlugin({})
      ]
    };
  }

  /*
   * Return the optimization object for consumption by webpack.
   */
  return optimization;
};
