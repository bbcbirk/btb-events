module.exports = (env) => {
  'use strict';

  /*
   * Import core utilities.
   * This is where custom functionality is setup for use in the webpack configuration.
   */
  const utils = require('./utils/core')(env);

  /*
   * Define project configuration object.
   */
  let projectConfig = {};

  /*
   * Define project paths used to setup entries.
   */
  projectConfig.relativeRootPath = './';
  projectConfig.relativeBlockRootPath = projectConfig.relativeRootPath + 'src/Core/Blocks/';
  projectConfig.relativePluginRootPath = projectConfig.relativeRootPath + 'src/';


  /*
   * Define project variable 'app name' and url.
   * The purpose of this is to generate a url to be used by the browsersync plugin (i.e. in case one needs hot-module-reloading).
   */
  projectConfig.appName = 'boilerplate-app-name';
  projectConfig.url = projectConfig.appName + '.test';

  projectConfig.entries = {};
  projectConfig.blockEntries = {};
  projectConfig.pluginEntries = {};

  projectConfig.blockEntries = utils.getBlockEntries(projectConfig.relativeBlockRootPath);
  projectConfig.pluginEntries = utils.getPluginEntries(projectConfig.relativePluginRootPath);

  projectConfig.entries = Object.assign({}, projectConfig.blockEntries, projectConfig.pluginEntries);


  /*
   * Return the project configuration object for consumption by the rest of the webpack configuration.
   */
  return projectConfig;
};
