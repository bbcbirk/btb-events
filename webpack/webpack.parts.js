module.exports = (env) => {
  'use strict';

  /*
   * Import individual webpack parts.
   * This is where rules for various asset types and optimization are declared and plugins are setup for the different environments.
   */
  let scripts = require('./parts/scripts')(env);
  let styles = require('./parts/styles')(env);
  let plugins = require('./parts/plugins')(env);
  let optimization = require('./parts/optimization')(env);

  /*
   * Initiate parts object.
   */
  let parts = {};
  parts.rules = [];
  parts.plugins = [];
  parts.optimization = {};

  /*
   * Define part rules for each individual part based on the according part/<asset-type> file.
   */
  parts.rules = [
    scripts.rules,
    styles.rules,
  ];

  /*
   * Define plugins based on the parts/plugin file
   */
  parts.plugins = plugins;

  /*
   * Define optimization rules based on the parts/optimization file
   */
  parts.optimization = optimization;

  /*
   * Return the parts object for consumption by webpack.
   */
  return parts;
};
