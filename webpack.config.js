const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');


module.exports =
{
  plugins: [
    new MiniCssExtractPlugin({
      filename: ({ chunk }) => `${chunk.name.replace('/js/', '/css/')}.min.css`,
    }),
  ],
  mode: "none",
  entry: {
    'public/js/bundle': [
      "./public/js/flex-maps-public.js",
      "./public/css/marker.css",
      "./public/css/spinner.css",
      "./public/css/location-element.css",
    ],
    'admin/js/bundle': [
      "./admin/js/flex-maps-admin.js",
      "./admin/js/flex-maps-acf.js",
      "./admin/css/acf-styles.css",
      "./public/css/marker.css",
      "./public/css/spinner.css",
      "./admin/css/styles.css",
    ],
  },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname),
    // publicPath: "",
  },
  resolve: {
    alias: {
       Public: path.resolve(__dirname, 'public/js/'),
       PublicCSS: path.resolve(__dirname, 'public/css/'),

       Admin: path.resolve(__dirname, 'admin/js/'),
       AdminCSS: path.resolve(__dirname, 'admin/css/'),

       FMGoogleMap: path.resolve(__dirname, 'includes/js/FM-Google-Map.js'),
    }
  },
  module: {
    rules: [
      {
        test: /\.css$/i,
        use: [MiniCssExtractPlugin.loader, "css-loader"],
      },
    ],
  },
  optimization: {
    minimize: true,
    minimizer: [
      `...`,
      new CssMinimizerPlugin(),
    ],
  },
}
