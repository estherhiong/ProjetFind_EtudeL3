const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .react() // Si tu utilises React
   .postCss('resources/css/app.css', 'public/css', [
       //
   ]);

// Surcharge complète de Webpack pour filtrer source-map-loader sur react-datepicker
mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.js$/,
                enforce: 'pre',
                use: [
                    {
                        loader: 'source-map-loader',
                    }
                ],
                exclude: [
                    /node_modules\/react-datepicker/,
                ],
            },
        ],
    },
    // Important : éviter les sourcemaps si non nécessaires
    devtool: 'eval-source-map' // ou false si tu veux les désactiver complètement
});
