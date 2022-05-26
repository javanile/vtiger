var gulp = require('gulp'),
    jshint = require('gulp-jshint'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify');

gulp.task('scripts', function() {
    gulp.src('./instafilta.js')
        .pipe(jshint())
        .pipe(rename('instafilta.min.js'))
        .pipe(uglify({
            preserveComments: 'some'
        }))
        .pipe(gulp.dest('./'));
});

gulp.task('default', function() {
    gulp.watch('instafilta.js', ['scripts']);
});