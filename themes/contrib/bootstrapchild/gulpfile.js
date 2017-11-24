var gulp = require('gulp'),
  browsersync = require('browser-sync'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps'),
  notify = require("gulp-notify"),
  watch = require('gulp-watch'),
  dest = require('gulp-dest'),
  combineMq = require('gulp-combine-mq'),
  cleanCSS = require('gulp-clean-css'),
  util = require('gulp-util'),
  rename = require('gulp-rename'),
  $ = require('gulp-load-plugins')();

var cssCleanLocation = ['./css/*.css', '!./css/*.min.css'];

/**
 * Start browsersync task and then watch files for changes
 */
gulp.task('browsersync', function () {
  browsersync.init({
    proxy: "http://tac.loc",
    open: false,
    reloadDelay: 1,
    reloadOnRestart: true,
    files: cssCleanLocation,
    middleware: require("serve-static")("./")
  });
});

gulp.task('default', ['sass', 'browsersync'], function () {
  gulp.watch('./scss/**/*.scss', ['sass']);
  gulp.watch(cssCleanLocation,['clean-css']);
});

gulp.task('clean-css', function() {
  return gulp.src(cssCleanLocation)
    .pipe(cleanCSS({ compatibility: 'ie9' }, function(details) {
      console.log(details.name + ': ' + details.stats.originalSize);
      console.log(details.name + ': ' + details.stats.minifiedSize);
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('./css'));
});

gulp.task('sass', function () {
  return gulp.src('./scss/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'nested',
      precision: 10,
      errLogToConsole: true
    }).on('error', sass.logError))
    .pipe(combineMq({ beautify: true }))
    .pipe($.autoprefixer({ browsers: ['last 2 versions', 'ie >= 9'] }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./css'))
    .pipe(browsersync.stream({ match: 'css/*.css' }))
});

