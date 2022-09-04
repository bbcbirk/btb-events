module.exports = (env) => {
  'use strict';

  /*
   * Import global/vendor dependencies.
   */
  const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
  const BundleAnalyzerPl = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
  const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin');
  const MiniCssExtractPlugin = require("mini-css-extract-plugin");
  const path = require('path');
  const StyleLintPl = require('stylelint-webpack-plugin');
  const Stylish = require('webpack-stylish'); // beautifies/provides better output for the shell console.

  /*
   * Import project configuration.
   * This is where we define the project specific configuration for use across the webpack configuration.
   */
  const projectConfig = require('../project.config')(env);

  /*
   * Setup boolean check to toggle configuration settings based on whether the environment should be optimized for production or not.
   */
  const optimizeForProduction = (typeof env !== 'undefined' && env && env.production === true);

  /*
   * Initiate plugins array.
   */
  let plugins = [];

  /*
   * setup default plugins
   */
  plugins.push(
    /*
     * Extracts and parses, (post-)css.
     */
    new MiniCssExtractPlugin({
      filename: (optimizeForProduction ? '[name].min.css' : '[name].css'),
      chunkFilename: (optimizeForProduction ? '[name].[chunkhash].min' : '[name].[chunkhash].css'),
    }),
    /*
     * Parses (posts-)css files and returns errors and warnings in the shell console.
     */
    new StyleLintPl({
      configFile: path.resolve(__dirname, '..', '..', 'stylelint.config.js'),
      files: [
        '**/*.css',
      ]
    }),
    /*
     * Provides an opinionated, differently styled output and overview of the webpack output to the shell console.
     */
    new Stylish(),
    /*
     * Improves error handling in webpack compilation.
     */
    new FriendlyErrorsWebpackPlugin()
  );

  /*
   * browsersync
   */
  if (typeof env !== 'undefined' && env && env.browsersync === true) {
    /*
     * Spins up a localhost webserver given a port and url, loads up the default browser on the client machine and auto-refreshes upon saving any changes to the webpack entry assets.
     */
    plugins.push(
      new BrowserSyncPlugin({
        host: 'localhost',
        port: 1337,
        proxy: projectConfig.url
      })
    );
  }

  /*
   * analyze
   */
  if (typeof env !== 'undefined' && env && (env.production === true) && (env.analyze === true)) {
    /*
     * Spins up a localhost webserver, loads up the default browser on the client machine and outputs stats and a visualization of the size of the entire webpack bundle.
     * Defaults to the production bundle.
     */
    plugins.push(
      new BundleAnalyzerPl()
    );
  }

  /*
   * Return the optimization object for consumption by webpack.
   */
  return plugins;
};
