module.exports = (env) => {
  'use strict';

  /*
   * Import core utilities.
   * This is where custom functionality is setup for use in the webpack configuration.
   */
  const utils = require('./webpack/utils/core')(env);

  /*
   * Import config modules.
   * This is where the general configuration is setup.
   */
  const configCommon = require('./webpack/common.config')(env);

  /*
   * Output the environment arguments to the (shell) console.
   */
  utils.logToConsole(env);

  /*
   * Return the configuration object for consumption by webpack.
   */
  return configCommon;
};
