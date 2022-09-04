module.exports = (env) => {
  'use strict';

  /*
   * Import global/vendor dependencies.
   */
  const fs = require('fs');
  const path = require('path');
  const slash = require('slash');
  /*
   * Setup boolean check to toggle configuration settings based on whether the environment should be optimized for production or not.
   */
  const optimizeForProduction = (typeof env !== 'undefined' && env && env.production === true);

  /*
   * Initiate utility object.
   */
  let utils = {};

  /*
   * Return all directories given a source relative path to the current working of the process - i.e. the webpack root.
   */
  utils.getDirectories = (srcPath) => {
    srcPath = slash(srcPath);
    if (utils.checkIfFileExists(srcPath)) {
      return fs.readdirSync(srcPath)
        .map(file => path.join(srcPath, file))
        .filter(path => fs.statSync(path).isDirectory());
    }
  };

  /*
   * Checks if a file exists, given a relative path to the current working of the process - i.e. the webpack root.
   */
  utils.checkIfFileExists = (file) => {
    file = slash(file);
    let fileExists = false;
    try {
      if (fs.existsSync(file)) {
        fileExists = true;
        return fileExists;
      }
    } catch (err) {
      console.error(err);
      return fileExists;
    }
  };

  /*
   * Return (all) block directory/directories given a source relative path to the current working of the process - i.e. the webpack root.
   * Excludes node modules and webpack directories.
   */
  utils.getBlockDirectories = (srcPath) => {
    srcPath = slash(srcPath);
    let subDirectories = utils.getDirectories(srcPath);
    let blockDirectories = [];

    if (subDirectories) {
      subDirectories.forEach((directory) => {
        directory = slash(directory);
        if (directory !== 'node_modules' && directory !== 'webpack') {
          blockDirectories.push(directory);
        }
      });
    }

    return blockDirectories;
  };

  /*
   * Returns block assets as webpack entry key/value pairs if asset(s) exist.
   Iterates over (all) block directory/directories given a source relative path to the current working of the process - i.e. the webpack root.
   */
  utils.getBlockEntries = (srcPath) => {
    srcPath = slash(srcPath);
    let blockDirectories = utils.getBlockDirectories(srcPath);
    let entries = {};

    if (blockDirectories) {
      blockDirectories.forEach((directory) => {
        directory = slash(directory);
        if (utils.checkIfFileExists(directory + '/src/css/editor.css')) {
          entries['./' + directory + '/dist/css/editor'] = './' + directory + '/src/css/editor.css';
        }

        if (utils.checkIfFileExists(directory + '/src/css/style.css')) {
          entries['./' + directory + '/dist/css/style'] = './' + directory + '/src/css/style.css';
        }

        if (utils.checkIfFileExists(directory + '/src/js/block.js')) {
          entries['./' + directory + '/dist/js/block' + (optimizeForProduction ? '.min' : '') + '.js'] = './' + directory + '/src/js/block.js';
        }
      });
    }

    return entries;
  };

  /*
   * Return (all) plugin directory/directories given a source relative path to the current working of the process - i.e. the webpack root.
   * Excludes node modules and webpack directories.
   */
  utils.getPluginDirectories = (srcPath) => {
    srcPath = slash(srcPath);
    let subDirectories = utils.getDirectories(srcPath);
    let pluginDirectories = [];

    if (subDirectories) {
      subDirectories.forEach((directory) => {
        directory = slash(directory);
        if (directory === 'src/assets') {
          pluginDirectories.push(directory);
        }
      });
    }

    return pluginDirectories;
  };

  /*
   * Returns plugin assets as webpack entry key/value pairs if asset(s) exist.
   Iterates over (all) plugin directory/directories given a source relative path to the current working of the process - i.e. the webpack root.
   */
  utils.getPluginEntries = (srcPath) => {
    srcPath = slash(srcPath);
    let pluginDirectories = utils.getPluginDirectories(srcPath);
    let entries = {};

    if (pluginDirectories) {
      pluginDirectories.forEach((directory) => {
        directory = slash(directory);
        if (utils.checkIfFileExists(directory + '/entries/base.js')) {
          entries['./' + directory + '/dist/js/base' + (optimizeForProduction ? '.min' : '') + '.js'] = './' + directory + '/entries/base.js';
        }

        if (utils.checkIfFileExists(directory + '/entries/base-admin.js')) {
          entries['./' + directory + '/dist/js/base-admin' + (optimizeForProduction ? '.min' : '') + '.js'] = './' + directory + '/entries/base-admin.js';
        }

        if (utils.checkIfFileExists(directory + '/css/base.css')) {
          entries['./' + directory + '/dist/css/base'] = './' + directory + '/css/base.css';
        }

        if (utils.checkIfFileExists(directory + '/admin/css/base-admin.css')) {
          entries['./' + directory + '/dist/css/base-admin'] = './' + directory + '/admin/css/base-admin.css';
        }
      });
    }

    return entries;
  };


  /*
   * Output the environment arguments to the (shell) console.
   */
  utils.logToConsole = (arg) => {
    console.log('Environment:');
    console.table(arg);
  };

  /*
   * Return the utility object for consumption by webpack and the project configuration.
   */
  return utils;
};
