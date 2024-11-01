/** the CodeCaste Team **/
(function($){
    if(wp.media == undefined)
        return;
    var Library = wp.media.controller.Library,
        FeaturedImageLibrary = wp.media.controller.FeaturedImage,
        l10n =  wp.media.view.l10n;
    
    var oldSelectMediaFrame = wp.media.view.MediaFrame.Select;
    wp.media.view.MediaFrame.Select = oldSelectMediaFrame.extend({
        initialize: function() {
            oldSelectMediaFrame.prototype.initialize.apply( this, arguments );
        },

        bindHandlers: function() {
            oldSelectMediaFrame.prototype.bindHandlers.apply( this, arguments );

            this.on( 'content:create:external', this.externalContent, this );
        },
        
        browseRouter: function( routerView ) {
            oldSelectMediaFrame.prototype.browseRouter.apply( this, arguments );
            routerView.set({
                external: {
                    text:     'External Media Library',
                    priority: 60
                }
            });
        },
        
        browseContent: function( contentRegion ) {
            var state = this.state();

            this.$el.removeClass('mode-external').addClass('mode-browse');

            this.$el.removeClass('hide-toolbar');

            // Browse our library of attachments.
            contentRegion.view = new wp.media.view.AttachmentsBrowser({
                controller: this,
                collection: state.get('library'),
                selection:  state.get('selection'),
                model:      state,
                sortable:   state.get('sortable'),
                search:     state.get('searchable'),
                filters:    state.get('filterable'),
                date:       state.get('date'),
                display:    state.has('display') ? state.get('display') : state.get('displaySettings'),
                dragInfo:   state.get('dragInfo'),

                idealColumnWidth: state.get('idealColumnWidth'),
                suggestedWidth:   state.get('suggestedWidth'),
                suggestedHeight:  state.get('suggestedHeight'),

                AttachmentView: state.get('AttachmentView')
            });
        },
        
        externalContent: function( contentRegion ) {
            var state = this.state();

            this.$el.removeClass('mode-browse').addClass('mode-external');

            var options = this.options;

            this.$el.removeClass('hide-toolbar');

            // Browse our library of attachments.
            contentRegion.view = new wp.media.view.AttachmentsBrowser({
                controller: this,
                collection: wp.media.externalquery( _.defaults({
                    isExternal: true,
                    orderby: 'menuOrder',
                    order: 'ASC'
                }, options.library ) ),
                selection:  state.get('selection'),
                model:      state,
                sortable:   state.get('sortable'),
                search:     state.get('searchable'),
                filters:    state.get('filterable'),
                date:       state.get('date'),
                display:    state.has('display') ? state.get('display') : state.get('displaySettings'),
                dragInfo:   state.get('dragInfo'),

                idealColumnWidth: state.get('idealColumnWidth'),
                suggestedWidth:   state.get('suggestedWidth'),
                suggestedHeight:  state.get('suggestedHeight'),

                AttachmentView: state.get('AttachmentView')
            });
        }
    });
    
    // External library extended
    wp.media.controller.ExternalLibrary = Library.extend({
        defaults: {
            id:         'insert',
            multiple:   'add', // false, 'add', 'reset'
            describe:   false,
            toolbar:    'select',
            sidebar:    'settings',
            content:    'upload',
            router:     'browse',
            menu:       'default',
            date:       false,
            external:   true,
            searchable: true,
            filterable: false,
            sortable:   false,
            autoSelect: true,

            // Allow local edit of attachment details like title, caption, alt text and description
            allowLocalEdits: true,

            // Uses a user setting to override the content mode.
            contentUserSetting: true,

            // Sync the selection from the last state when 'multiple' matches.
            syncSelection: true
        }
    });
    
    var oldPostMediaFrame = wp.media.view.MediaFrame.Post;
    wp.media.view.MediaFrame.Post = oldPostMediaFrame.extend({
        initialize: function() {
            oldPostMediaFrame.prototype.initialize.apply( this, arguments );
        },

        createStates: function() {
            oldPostMediaFrame.prototype.createStates.apply( this, arguments );

            var options = this.options;
            
            this.states.remove({id: 'insert'});
            
            this.states.add([
                new wp.media.controller.ExternalLibrary({                    
                    id:         'insert',
                    title:      l10n.insertMediaTitle,
                    priority:   30,
                    toolbar:    'main-insert',
                    filterable: 'all',
                    library:  wp.media.query( options.library ),
                    multiple:   options.multiple ? 'reset' : false,
                    editable:   true,            
                    // If the user isn't allowed to edit fields,
                    // can they still edit it locally?
                    allowLocalEdits: true,
                    // Show the attachment display settings.
                    displaySettings: true,
                    // Update user settings when users adjust the
                    // attachment display settings.
                    displayUserSettings: true,
                    
                    AttachmentView: wp.media.view.Attachment.Library
                })
            ]);
        },
        
        bindHandlers: function() {
            oldPostMediaFrame.prototype.bindHandlers.apply( this, arguments );

            this.on( 'content:create:external', this.externalContent, this );
        },
        
        browseRouter: function( routerView ) {
            oldPostMediaFrame.prototype.browseRouter.apply( this, arguments );
            
            routerView.set({
                external: {
                    text:     'External Media Library',
                    priority: 60
                }
            });
        },
        
        browseContent: function( contentRegion ) {
            var state = this.state();
            
            this.$el.removeClass('mode-external').addClass('mode-browse');
            
            this.$el.removeClass('hide-toolbar');

            // Browse our library of attachments.
            contentRegion.view = new wp.media.view.AttachmentsBrowser({
                controller: this,
                collection: state.get('library'),
                selection:  state.get('selection'),
                model:      state,
                sortable:   state.get('sortable'),
                search:     state.get('searchable'),
                filters:    state.get('filterable'),
                date:       state.get('date'),
                display:    state.has('display') ? state.get('display') : state.get('displaySettings'),
                dragInfo:   state.get('dragInfo'),

                idealColumnWidth: state.get('idealColumnWidth'),
                suggestedWidth:   state.get('suggestedWidth'),
                suggestedHeight:  state.get('suggestedHeight'),

                AttachmentView: state.get('AttachmentView')
            });
        },

        externalContent: function( contentRegion ) {
            var state = this.state();
            
            this.$el.removeClass('mode-browse').addClass('mode-external');
            
            var options = this.options;
            
            this.$el.removeClass('hide-toolbar');

            // Browse our library of attachments.
            contentRegion.view = new wp.media.view.AttachmentsBrowser({
                controller: this,
                collection: wp.media.externalquery( _.defaults({
                    isExternal: true,
                    orderby: 'menuOrder',
                    order: 'ASC'
                }, options.library ) ),
                selection:  state.get('selection'),
                model:      state,
                sortable:   state.get('sortable'),
                search:     state.get('searchable'),
                filters:    state.get('filterable'),
                date:       state.get('date'),
                display:    state.has('display') ? state.get('display') : state.get('displaySettings'),
                dragInfo:   state.get('dragInfo'),

                idealColumnWidth: state.get('idealColumnWidth'),
                suggestedWidth:   state.get('suggestedWidth'),
                suggestedHeight:  state.get('suggestedHeight'),

                AttachmentView: state.get('AttachmentView')
            });
        },
        
        mainInsertToolbar: function( view ) {
            oldPostMediaFrame.prototype.mainInsertToolbar.apply( this, arguments );
            
            var controller = this;

            this.selectionStatusToolbar( view );

            view.set( 'insertOptimised', {
                style: 'primary',
                priority: 80,
                text:     l10n.sioExternalLibrary.insertOptimisedImageIntoPost,
                requires: { selection: true },
                
                click: function() {
                    var state = controller.state(),
                        selection = state.get('selection');
                    
                    var arrayOfSelection = [];
                    selection.forEach(function(m) {
                        arrayOfSelection.push(m.get('id'));
                    });
                    $('.media-button-insertOptimised').html("Processing...");
                    wp.media.ajax({
                        data: {
                            action:  'sio_ajax_upload_to_ftp',
                            selected: arrayOfSelection
                        }
                    }).done(function(response) {
                        $('.media-modal.wp-core-ui').removeClass('loading');
                        controller.close();
                        selection.each(function(attachment, i) { attachment.set('id', response[i]); });
                        state.trigger( 'insert', selection ).reset();
                    });
                }
            });
        }
    });
    
    FeaturedImageLibrary.prototype.defaults.displaySettings = true;
    
    wp.media.controller.OptimisedFeaturedImage = FeaturedImageLibrary.extend({
        defaults: _.defaults({
            id:            'optimised-featured-image',
            title:         'Optimised Featured Image',
            multiple:      false,
            filterable:    'uploaded',
            toolbar:       'optimised-featured-image',
            priority:      60,
            syncSelection: true,
            displaySettings: true
        }, Library.prototype.defaults ),

        initialize: function() {
            FeaturedImageLibrary.prototype.initialize.apply( this, arguments );
        },

        activate: function() {
            FeaturedImageLibrary.prototype.activate.apply( this, arguments );
        },
        
        deactivate: function() {
            FeaturedImageLibrary.prototype.deactivate.apply( this, arguments );
        },

        updateSelection: function() { 
            FeaturedImageLibrary.prototype.updateSelection.apply( this, arguments );
        }
    });
    
    wp.media.externalquery = function( props ) {
        return new wp.media.model.ExternalAttachments( null, {
            props: _.extend( _.defaults( props || {}, { orderby: 'date' } ), { query: true } )
        });
    };
    
    wp.media.model.ExternalAttachments = wp.media.model.Attachments.extend({
        initialize: function() {
            wp.media.model.Attachments.prototype.initialize.apply( this, arguments );
        },
        _requery: function(refresh) {
            var props;
            if ( this.props.get('query') ) {
                props = this.props.toJSON();
                //props.cache = ( true !== refresh );
                props.cache = false;
                this.mirror( wp.media.model.ExternalQuery.get( this.props.toJSON() ) );
            }
        }
    });
    
    wp.media.model.ExternalQuery = wp.media.model.Query.extend({
        initialize: function() {
            wp.media.model.Query.prototype.initialize.apply( this, arguments );
        },
        sync: function( method, model, options ) {
            var fallback;

            // Overload the read method so Attachment.fetch() functions correctly.
            if ( 'read' === method ) {
                options = options || {};
                options.context = this;
                options.data = _.extend( options.data || {}, {
                    action:  'query-external-attachments',
                    post_id: wp.media.model.settings.post.id
                });

                // Clone the args so manipulation is non-destructive.
                args = _.clone( this.args );

                // Determine which page to query.
                if ( -1 !== args.posts_per_page ) {
                    args.paged = Math.floor( this.length / args.posts_per_page ) + 1;
                }
                
                options.data.query = args;
                return wp.media.ajax( options );

                // Otherwise, fall back to Backbone.sync()
            } else {
                fallback = wp.media.model.Attachments.prototype.sync ? wp.media.model.Attachments.prototype : Backbone;
                return fallback.sync.apply( this, arguments );
            }
        }
    }, {
        // Caches query objects so queries can be easily reused.
        get: (function(){
            var queries = [];
            return function( props, options ) {
                var args     = {},
                    orderby  = wp.media.model.ExternalQuery.orderby,
                    defaults = wp.media.model.ExternalQuery.defaultProps,
                    query,
                    cache =  props.cache;

                // Remove the `query` property. This isn't linked to a query,
                // this *is* the query.
                delete props.query;

                // Remove the `remotefilters` property. 
                delete props.remotefilters;

                // Remove the `uioptions` property. 
                delete props.uioptions;

                // Fill default args.
                _.defaults( props, defaults );

                // Normalize the order.
                props.order = props.order.toUpperCase();
                if ( 'DESC' !== props.order && 'ASC' !== props.order )
                    props.order = defaults.order.toUpperCase();

                // Ensure we have a valid orderby value.
                if ( ! _.contains( orderby.allowed, props.orderby ) )
                    props.orderby = defaults.orderby;

                // Generate the query `args` object.
                // Correct any differing property names.
                _.each( props, function( value, prop ) {
                    if ( _.isNull( value ) )
                        return;

                    args[ wp.media.model.ExternalQuery.propmap[ prop ] || prop ] = value;
                });

                // Fill any other default query args.
                _.defaults( args, wp.media.model.ExternalQuery.defaultArgs );

                // `props.orderby` does not always map directly to `args.orderby`.
                // Substitute exceptions specified in orderby.keymap.
                args.orderby = orderby.valuemap[ props.orderby ] || props.orderby;

                // Search the query cache for a matching query.
                if ( cache ) {
                    query = _.find( queries, function( query ) {
                        return _.isEqual( query.args, args );
                    });
                } else {
                    queries = [];
                }
                
                // Otherwise, create a new query and add it to the cache.
                if ( ! query ) {
                    query = new wp.media.model.ExternalQuery( [], _.extend( options || {}, {
                        props: props,
                        args:  args
                    } ) );
                    queries.push( query );
                }

                return query;
            };
        }())
    });
    
    wp.media.featuredImage.set = function( id ) {
        var settings = wp.media.view.settings;

        settings.post.featuredImageId = id;
        var selectedSize = $('.attachment-display-settings select[name="size"]').find(":selected").val();
        wp.media.post( 'sio-get-post-thumbnail-html', {
            post_id:      settings.post.id,
            thumbnail_id: settings.post.featuredImageId,
            size: selectedSize,
            _wpnonce:     settings.post.nonce
        }).done( function( html ) {
            if ( html == '0' ) {
                window.alert( window.setPostThumbnailL10n.error );
                return;
            }
            $( '.inside', '#postimagediv' ).html( html );
        });
    };
    
    wp.media.optimisedFeaturedImage = {
        get: function() {
            return wp.media.view.settings.post.featuredImageId;
        },
        set: function( id ) {
            var settings = wp.media.view.settings;

            settings.post.featuredImageId = id;
            
            $('#postimagediv .loading').removeClass('hidden');
            var selectedSize = $('.attachment-display-settings select[name="size"]').find(":selected").val();
            wp.media.post( 'sio_ajax_upload_to_ftp' , {
                post_id:      settings.post.id,
                selected: settings.post.featuredImageId,               
                _wpnonce:     settings.post.nonce
            }).done(function(newFeaturedImageId) {
                wp.media.post( 'sio-get-post-thumbnail-html', {
                    post_id:      settings.post.id,
                    thumbnail_id: newFeaturedImageId[0],
                    size: selectedSize,
                    _wpnonce:     settings.post.nonce
                }).done( function( html ) {
                    if ( html == '0' ) {
                        window.alert( window.setPostThumbnailL10n.error );
                        return;
                    }
                    $( '.inside', '#postimagediv' ).html( html );
                    $('#postimagediv .loading').addClass('hidden');
                });
            });            
        },
        remove: function() {
            wp.media.optimisedFeaturedImage.set( -1 );
        },
        frame: function() {
            if ( this._frame ) {
                wp.media.frame = this._frame;
                return this._frame;
            }

            this._frame = wp.media({
                state: 'optimised-featured-image',
                states: [ new wp.media.controller.OptimisedFeaturedImage() , new wp.media.controller.EditImage() ]
            });

            this._frame.on( 'toolbar:create:optimised-featured-image', function( toolbar ) {
                this.createSelectToolbar( toolbar, {
                    text: 'Set optimised featured image'
                });
            }, this._frame );

            this._frame.on( 'content:render:edit-image', function() {
                var selection = this.state('optimised-featured-image').get('selection'),
                    view = new wp.media.view.EditImage( { model: selection.single(), controller: this } ).render();

                this.content.set( view );

                view.loadEditor();

            }, this._frame );

            this._frame.state('optimised-featured-image').on( 'select', this.select );
            return this._frame;
        },
        /**
		 * 'select' callback for Featured Image workflow, triggered when
		 *  the 'Set Featured Image' button is clicked in the media modal.
		 *
		 * @this wp.media.controller.FeaturedImage
		 */
        select: function() {
            var selection = this.get('selection').single();

            if ( ! wp.media.view.settings.post.featuredImageId ) {
                return;
            }

            wp.media.optimisedFeaturedImage.set( selection ? selection.id : -1 );
        },
        /**
		 * Open the content media manager to the 'featured image' tab when
		 * the post thumbnail is clicked.
		 *
		 * Update the featured image id when the 'remove' link is clicked.
		 */
        init: function() {
            $('#postimagediv').append('<div class="loading hidden"></div>');
            $('#postimagediv').on( 'click', '#set-optimised-post-thumbnail', function( event ) {
                event.preventDefault();
                // Stop propagation to prevent thickbox from activating.
                event.stopPropagation();

                wp.media.optimisedFeaturedImage.frame().open();
            });
        }
    };

    $( wp.media.optimisedFeaturedImage.init );
}(jQuery));