module.exports = function ( grunt ) {
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		uglify: {
			js: {
				files: {
					'build/js/form-manager.min.js': ['build/js/form-manager.js'],
					'build/js/form-mce.min.js': ['js/form-mce.js'],
					'build/js/form-cpt-preview.min.js': ['js/form-cpt-preview.js'],
					'build/js/form.min.js': ['js/form.js']
				}
			}
		},
		concat: {
			dist: {
				src: ['js/manager/utils.js', 'js/manager/mixins.js', 'js/manager/models.js', 'js/manager/collections.js', 'js/manager/views.js', 'js/manager/router.js', 'js/manager/app.js'],
				dest: 'build/js/form-manager.js'
			}
		},
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			all: ['js/*', 'js/manager/*']
		},
		sass: {
			dist: {
				files: {
					'build/css/form-manager.css': 'scss/form-manager.scss',
					'build/css/form-mce.css': 'scss/form-mce.scss',
					'build/css/form.css': 'scss/form.scss',
					'build/css/admin.css': 'scss/admin.scss',
					'build/css/form-cpt.css': 'scss/form-cpt.scss',
					'build/css/form-table.css': 'scss/form-table.scss'
				}
			}
		},
		cssmin: {
			dist: {
				files: [{
					expand: true,
					cwd: 'build/css/',
					src: ['*.css', '!*.min.css'],
					dest: 'build/css/',
					ext: '.min.css'
				}]
			}
		},
		qunit: {
			all: [ 'tests/js/*.html' ]
		},
		watch: {
			files: [
				'js/*',
				'js/manager/*',
				'scss/*'
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