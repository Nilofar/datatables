require('../scss/datatables.scss');

import DataTable from 'datatables.net-bs4';
import DataTableFixedHeader from 'datatables.net-fixedheader-bs4';
import DataTableResponsive from 'datatables.net-responsive-bs4';
import DataTableScroller from 'datatables.net-scroller-bs4';
import DataTableKeyTable from 'datatables.net-keytable-bs4';

require('./custom_omines_datatables.js');

export default class DatatablesConfig {

    constructor(options) {
        this.table = '';
        this.dt = '';
        this._divDatatable  = $('[data-plugin="datatable"]');
        this.settings       = this._divDatatable.data('settings');
        this._formFilter    = $('form[name="search_engine"]');
        this.id             = this._divDatatable.attr('id');
        this.identifier     = this._divDatatable.data('identifier');
        this.options = {
            dom : '<"row"<"col-3"l><"col-6 text-center"i><"col-3"f>>rt<"row"<"col-12 text-center"i>><"row"<"col-12"p>><"clear">',
            //responsive : true,
            //keys : true,
            responsive: {
                details: {
                    renderer: function ( api, rowIdx, columns ) {
                        let data = $.map( columns, function ( col, i ) {
                            return col.hidden ?
                                '<div class="row row-details" data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                                    '<div class="col-3">'+col.title+':'+'</div> '+
                                    '<div class="col-9">'+col.data+'</div>'+
                                '</div>' :
                                '';
                        } ).join('');

                        return data ?
                            $('<div/>').append( data ) :
                            false;
                    }
                }
            },
            scrollX : 0,
            scrollY: 400,
            deferRender:    true,
            // fixedHeader: {
            //     header: true,
            //     headerOffset: $('.site-navbar').outerHeight()
            // },
            //stateSave:      true,
            // stateSaveCallback: function(settings,data) {
            //     localStorage.setItem( 'DataTables_' + settings.sInstance, JSON.stringify(data) )
            // },
            // stateLoadCallback: function(settings) {
            //     return JSON.parse( localStorage.getItem( 'DataTables_' + settings.sInstance ) )
            // },
            //pageLength: 100,
            //searching: true,
            //  scrollY:        400,
            //  scrollCollapse: true,
            //  scroller:       {
            //      displayBuffer: 10
            // },
            //paging: true,
        }
    }

    addOption(name, value) {
        this.options[name] = value;
    }

    removeOption(name) {
        delete this.options[name];
    }
    // Initialisation avec custom options ou options par défaut + default then callback
    initialize(options) {
        let $this = this;
        this.customInitialize(options);
        this.addThenCallback(function(dt){
            $this.initDefaultThenCallback(dt);
        });
    }

    customInitialize(options) {
        if (options !== undefined) {
            this.options = options;
        }

        this.table =
            this._divDatatable.initDataTables(this.settings, this.options)
    }

    /**
     * Permet d'ajouter un custom then.
     *
     * @param callback
     */
    addThenCallback(callback) {
        let $this = this;
        this.table.then(function(dt) {
            $this.dt = dt;
            callback(dt);
        });
    }

    /**
     * Default then callback : permet d'ajout les filtres via le formulaire.
     * Lance une requete ajax afin de sauvegarder en session les elements selectionnés / remplis du formulaire
     * pour pouvoir les utiliser dans le builder de requete coté back.
     *
     * @param dt
     */
    initDefaultThenCallback(dt) {

        let $this = this;

        if ($this._formFilter.length > 0) {

            $this._formFilter.on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    context : this,
                    data: $this._formFilter.serialize(),
                    url: Routing.generate('edulog_datatables_saveFilters', {
                        identifier : $this.identifier
                    })
                })
                    .fail(function(e){
                        toastr['error']("Une erreur est survenue, impossible de charger les données");
                    })
                    .done(function(json){
                        dt.draw();
                    });

            });
        }
    }
};


















