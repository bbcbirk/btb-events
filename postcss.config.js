module.exports = {
  plugins: {
    'postcss-reporter': {
      clearReportedMessages: true
    },
    'postcss-import': {},
    'autoprefixer': {
      grid: true,
    },
    'postcss-nested': {},
    'postcss-preset-env': {
      stage: 4,
      features: {
        'custom-media-queries': true,
        'nesting-rules': true,
      }
    },
  },
};
