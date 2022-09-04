module.exports = {
  "extends": "stylelint-config-standard",
  rules: {
    "max-nesting-depth": 3,
    "indentation": ["tab", {
      "except": ["value"]
    }],
    "color-hex-case": null,
    "color-hex-length": null,
  },
  "ignoreFiles": [
    "node_modules/**/*.css",
    "node_modules/**/*.js",
    "vendor/**/*.css",
    "vendor/**/*.js",
    "src/assets/dist/**/*.css",
    "src/assets/dist/**/*.js",
    "src/assets/react-apps/**/*.css",
    "src/assets/react-apps/**/*.js",
    "src/Core/Blocks/**/dist/**/*.css",
    "src/Core/Blocks/**/dist/**/*.js",
  ]
};
