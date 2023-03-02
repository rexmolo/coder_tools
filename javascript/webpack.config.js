// A webpack config file for myself to remember how to use it

const path = require('path');
const glob = require('glob')
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const devMode = process.env.NODE_ENV || 'production';
const webpack = require('webpack');
const Dotenv = require('dotenv-webpack');


// fetch all js files in the entry folder
const ENTRY_PATH = path.resolve(__dirname, 'assets/js');
const entries = function() {
  const entryFiles = glob.sync(ENTRY_PATH + '/*.js')
  const map = {}

  entryFiles.forEach(filePath => {
    const filenameRegex = /([^\/]+)\.\w+$/;
    const filename = filenameRegex.exec(filePath)[1]; 
    map[filename] = filePath
  })
  return map
}

module.exports = {
    watch:true,
    mode:devMode,
    devtool:"source-map",
    entry: entries(),
    // {
    //     home:path.resolve(__dirname, 'assets/js/home.js'),
    //     item_category:path.resolve(__dirname, 'assets/js/item_category.js'),
    //     item_detail:path.resolve(__dirname, 'assets/js/item_detail.js'),
    //     checkout:path.resolve(__dirname, 'assets/js/checkout.js'),
    //     supplier_errands:path.resolve(__dirname, 'assets/js/supplier_errands.js'),
    //     supplier_order_detail:path.resolve(__dirname, 'assets/js/supplier_order_detail.js'),
    //     supplier_service_manage:path.resolve(__dirname, 'assets/js/supplier_service_manage.js')
    // },
    output:{
        path: path.resolve(__dirname, 'assets/dist'),
        filename:'js/[name].js',
        clean: true,
        assetModuleFilename:'resource/[name][ext]',
    },
    plugins :[
        new MiniCssExtractPlugin({  //extract css into separate files
            filename:"css/[name].css"
        }),
        new webpack.ProvidePlugin({ // inject ES5 modules as global vars
          $: "jquery",
          jQuery: "jquery"
      }),
        new Dotenv()  // load env variables
    ],
    module:{
        rules: [
            {
                // For pure CSS - /\.css$/i,
                // For Sass/SCSS - /\.((c|sa|sc)ss)$/i,
                // For Less - /\.((c|le)ss)$/i,
                test: /\.((c|sa|sc)ss)$/i,
                use: [
                    // "style-loader",
                     {
                    loader: MiniCssExtractPlugin.loader,
                    options: {
                        // hmr:process.env.NODE_ENV === 'development'
                        //  publicPath:"assets/sass/page",
                    }
                   },
                  {
                    loader: "css-loader",
                    options: {
                      // Run `postcss-loader` on each CSS `@import` and CSS modules/ICSS imports, do not forget that `sass-loader` compile non CSS `@import`'s into a single file
                      // If you need run `sass-loader` and `postcss-loader` on each CSS `@import` please set it to `2`
                      importLoaders: 1,
                    },
                  },
                  {loader: "postcss-loader",options: {postcssOptions:{
                    plugins:[
                        [
                            "postcss-preset-env",
                            {
                                //options
                            },
                        ]
                    ] ,
                  }},},
                  // Can be `less-loader`
                  {
                    loader: "sass-loader",
                    options:{
                      sourceMap: true,
                    }
                  },
                ],
              },
              {
                test: /\.(svg|png)$/,
                type: "asset/resource", // for resource files use asset/resource
                generator: {
                // will be emitted into dist/image 
                filename: "images/[name][ext]",
                },
              },
              {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                  loader: 'babel-loader',
                  options: {
                    presets: ['@babel/preset-env']
                  }
                }
              },
        ]
    },

}