module.exports = (env) => {
  'use strict';

  /*
   * Import global/vendor dependencies.
   */
  const MiniCssExtractPlugin = require("mini-css-extract-plugin");
  const path = require('path');

  /*
   * Setup boolean check to toggle configuration settings based on whether the environment should be optimized for production or not.
   */
  const optimizeForProduction = (typeof env !== 'undefined' && env && env.production === true);  

  /*
   * Initiate styles object.
   */
  let styles = {};

  /*
   * Define rules for consuming (post-)css assets.
   */
  styles.rules = {
    test: /\.css$/,
    use: [{
        loader: MiniCssExtractPlugin.loader,
        options: {}
      },
      {
        loader: 'css-loader',
        options: {
          importLoaders: 2,
          sourceMap: (optimizeForProduction ? false : true),
          url: false
        }
      },
      {
        loader: 'postcss-loader',
        options: {
          ident: 'postcss',
          sourceMap: (optimizeForProduction ? false : true),
          config: {
            path: path.resolve(process.cwd(), 'postcss.config.js')
          }
        }
      },
    ]
  };

  /*
   * Return the styles object for consumption by webpack.
   */
  return styles;
};
