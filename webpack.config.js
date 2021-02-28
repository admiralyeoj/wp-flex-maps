const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');


module.exports =
{
  plugins: [
    new MiniCssExtractPlugin({
      filename: ({ chunk }) => `${chunk.name.replace('/js/', '/css/')}.css`,
      attributes: {
        id: 'target',
        'data-target': 'example',
      },
    }),
  ],
  mode: "production",
  entry: {
    'public/dist/js/index': [
      "./public/src/js/flex-maps-public.js"
    ],
    'admin/dist/js/index': [
      "./admin/src/js/flex-maps-admin.js",
      "./admin/src/js/flex-maps-acf.js",
    ],
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname),
  },
  module: {
    rules: [
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },
    ],
  },
}