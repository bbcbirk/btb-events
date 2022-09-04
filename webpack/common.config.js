module.exports = (env) => {
  'use strict';

  /*
   * Import global/vendor dependencies.
   */
  const path = require('path');

  /*
   * Import project configuration.
   * This is where we define the project specific configuration for use across the webpack configuration.
   */
  const projectConfig = require('./project.config')(env);

  /*
   * Import individual webpack parts.
   * This is where rules for various asset types and optimization are declared and plugins are setup for the different environments.
   */
  const parts = require('./webpack.parts')(env);
  /*
   * Import core utilities.
   * This is where custom functionality is setup for use in the webpack configuration.
   */
  const utils = require('./utils/core')(env);

  /*
   * Setup boolean check to toggle configuration settings based on whether the environment should be optimized for production or not.
   */
  const optimizeForProduction = (typeof env !== 'undefined' && env && env.production === true);

  /*
   * Setup boolean check to toggle configuration settings based on whether the environment should be optimized for development or not.
   */
  const optimizeForDevelopment = (typeof env !== 'undefined' && env && env.development === true);

  /*
   * Initiate configuration object.
   */
  let config = {};

  /*
   * Setup source-maps for the development environment
   */
  if (optimizeForDevelopment) {
    config.devtool = 'source-map';
  }

  /*
   * Set mode based on environment argument.
   */
  config.mode = (optimizeForProduction ? 'production' : 'development');

  /*
   * Define output path and filename.
   * In order to achieve dynamical file naming, the path is set to the webpack root and the filename will dynamically handle the rest.
   * Please note that this pattern goes against the default webpack configuration (using a 'dist' directory).
   * The functionality for handling the filename resides in the utils/core.js file
   */
  config.output = {
    path: path.resolve(process.cwd()),
    filename: '[name]',
  };

  /*
   * 
   */
  config.optimization = parts.optimization;

  /*
   * Define entry file(s)
   * Returns an object with key/value pairs, dynamically generated in the core utilities file, iterating over the singular/various block directory/directories and mapping up the output : input paths for each asset.
   */
  // config.entry =  utils.getEntries(projectConfig.relativeRootPath);
  config.entry =  projectConfig.entries;


  /*
   * Define plugins.
   * Returns an array of plugins that are setup in the parts/plugin file, based on the environment argument(s).
   */
  config.plugins = parts.plugins;

  /*
   * Define rules for various assets.
   * Returns an array of objects containing rules for each asset type.
   * These are setup in the parts/<asset-type> file, based on the environment argument(s).
   */
  config.module = {};
  config.module.rules = parts.rules;

  /*
   * Suppress default webpack errors.
   * Instead these are handled by some additional plugins.
   * webpack-stylish and friendly-errors-webpack-plugin
   */
  config.stats = 'errors-only';

  /*
   * Return the configuration object for consumption by webpack.
   */
  return config;
};
