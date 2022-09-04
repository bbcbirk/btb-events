module.exports = (env) => {
  'use strict';
   
  /*
   * Initiate scripts object.
   */
  let scripts = {};

  /*
   * Define rules for consuming js or jsx assets.
   */
  scripts.rules = {
    test: /\.(js|jsx)$/,
    exclude: /node_modules/,
    use: {
      loader: 'babel-loader',
      options: {
        presets: ['@babel/preset-env']
      }
    },
  };

  /*
   * Return the scripts object for consumption by webpack.
   */
  return scripts;
};
