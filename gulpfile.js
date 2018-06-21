var del = require("del");
var gulp = require("gulp");
var yaml = require("yamljs");
var imagemin = require("gulp-imagemin");
var cleanCSS = require("gulp-clean-css");
var uglify = require("gulp-uglify");

var config = yaml.load("data/config.yml");

var src_paths = {
	img: "resources/img/**/*",
	css: "resources/css/**/*.css",
	js: "resources/js/**/*.js",
};

var dest_paths = {
	img: "public/assets/img",
	css: "public/assets/css",
	js: "public/assets/js"
};

gulp.task("clean-img", function() {
	return del(dest_paths.img);
});

gulp.task("clean-css", function() {
	return del(dest_paths.css);
});

gulp.task("clean-js", function() {
	return del(dest_paths.js);
});

gulp.task("clean", ["clean-img", "clean-css", "clean-js"]);

gulp.task("img", ["clean-img"], function() {
	var img_task = gulp.src(src_paths.img);

	if (config.environment.phase === "production") {
		img_task.pipe(imagemin());
	}

	img_task.pipe(gulp.dest(dest_paths.img));
});

gulp.task("css", ["clean-css"], function() {
	var css_task = gulp.src(src_paths.css);

	if (config.environment.phase === "production") {
		css_task.pipe(cleanCSS());
	}

	css_task.pipe(gulp.dest(dest_paths.css));
});

gulp.task("js", ["clean-js"], function() {
	var js_task = gulp.src(src_paths.js);

	if (config.environment.phase === "production") {
		js_task.pipe(uglify());
	}

	js_task.pipe(gulp.dest(dest_paths.js));
});

gulp.task("watch", ["default"], function() {
	gulp.watch(src_paths.img, ["img"]);
	gulp.watch(src_paths.css, ["css"]);
	gulp.watch(src_paths.js, ["js"]);
});

gulp.task("default", ["img", "css", "js"]);
