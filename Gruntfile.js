module.exports = function ( grunt ) {
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		uglify: {
			js: {
				files: {
					'assets/build/js/form-manager.min.js': ['assets/build/js/form-manager.js'],
					'assets/build/js/form-mce.min.js': ['assets/js/form-mce.js'],
					'assets/build/js/form-cpt-preview.min.js': ['assets/js/form-cpt-preview.js'],
					'assets/build/js/form.min.js': ['assets/js/form.js'],
					'assets/build/js/settings.min.js': ['assets/js/settings.js']
				}
			}
		},
		concat: {
			dist: {
				src: ['assets/js/manager/utils.js', 'assets/js/manager/mixins.js', 'assets/js/manager/models.js', 'assets/js/manager/collections.js', 'assets/js/manager/views.js', 'assets/js/manager/router.js', 'assets/js/manager/app.js'],
				dest: 'assets/build/js/form-manager.js'
			}
		},
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			all: ['assets/js/*', 'assets/js/manager/*']
		},
		sass: {
			dist: {
				files: {
					'assets/build/css/form-manager.css': 'assets/scss/form-manager.scss',
					'assets/build/css/form-mce.css': 'assets/scss/form-mce.scss',
					'assets/build/css/form.css': 'assets/scss/form.scss',
					'assets/build/css/admin.css': 'assets/scss/admin.scss',
					'assets/build/css/form-cpt.css': 'assets/scss/form-cpt.scss',
					'assets/build/css/form-table.css': 'assets/scss/form-table.scss',
					'assets/build/css/settings.css': 'assets/scss/settings.scss'
				}
			}
		},
		cssmin: {
			dist: {
				files: [{
					expand: true,
					cwd: 'assets/build/css/',
					src: ['*.css', '!*.min.css'],
					dest: 'assets/build/css/',
					ext: '.min.css'
				}]
			}
		},
		qunit: {
			all: [ 'tests/js/*.html' ]
		},
		watch: {
			files: [
				'assets/js/*',
				'assets/js/manager/*',
				'assets/scss/*'
			],
			tasks: ['concat:dist', 'uglify', 'sass', 'cssmin:dist']
		}
	} );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-jshint' );
	grunt.loadNpmTasks( 'grunt-contrib-sass' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-qunit' );
	grunt.registerTask( 'default', ['jshint', 'concat:dist', 'uglify:js', 'sass', 'cssmin:dist'] );
	grunt.registerTask( 'test', [ 'qunit:all' ] );
};