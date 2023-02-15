<!DOCTYPE html>
<html>
<head>
    <title>{{env('APPLICATION_NAME')}} - Home</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="/assets/ext-3.4.1/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="/assets/ext-3.4.1/resources/css/oxygen-icons.css" />

    <!-- overrides to base library -->
    <link rel="stylesheet" type="text/css" href="/assets/ext-3.4.1/ux/gridfilters/css/GridFilters.css" />
    <link rel="stylesheet" type="text/css" href="/assets/ext-3.4.1/ux/gridfilters/css/RangeMenu.css" />

    <!-- GC -->
    <!-- LIBS -->
    <script type="text/javascript" src="/assets/ext-3.4.1/adapter/ext/ext-base.js"></script>
    <!-- ENDLIBS -->

    <script type="text/javascript" src="/assets/ext-3.4.1/ext-all.js"></script>

    <!-- extensions -->
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/menu/RangeMenu.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/menu/ListMenu.js"></script>

    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/GridFilters.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/filter/Filter.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/filter/StringFilter.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/filter/DateFilter.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/filter/ListFilter.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/filter/NumericFilter.js"></script>
    <script type="text/javascript" src="/assets/ext-3.4.1/ux/gridfilters/filter/BooleanFilter.js"></script>

    <style type="text/css">
        .box {
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -400px;
            /* -1/2 width */
            margin-top: -300px;
            /* -1/2 height */
        }
    </style>
</head>
<body>
@include('includes.header_menu')
<div class = "container">
    <ol class = "breadcrumb">
        <li><a href = "{{url('/')}}">Home</a></li>
        <li class = "active">USPS Driver Manifest</li>
    </ol>
<div id="main-panel" class="box">

</div>
</div>
<script type="application/javascript">
    Ext.ns('Monogram','Monogram.Shipment','Monogram.Shipment.Manifest');

    Monogram.Shipment.GridPanel = Ext.extend(Ext.grid.EditorGridPanel, {
        stripeRows: true,
        loadMask: true,
        viewConfig: {
            forceFit: true
        },

        listeners: {
            'afterrender': function(grid) {
                grid.getStore().load({
                    params: {location: grid.locationValue}
                });
            }
        },

        locationValue: null,

        initComponent: function()
        {
            let self = this;
            self.store = self.configureStore();
            self.tbar = self.configureToolBar();
            self.sm = new Ext.grid.CheckboxSelectionModel(),
            self.cm = new Ext.grid.ColumnModel({
                defaults: {
                    width: 120,
                    sortable: true
                },
                columns: [
                    self.sm,
                    {id: 'id', header: 'Id', sortable: true, dataIndex: 'id', hidden: true},
                    {id: 'ship_id', header: 'Ship Id', sortable: true, dataIndex: 'ship_id', hidden: true},
                    {id: 'ship_from', header: 'Shipped From', sortable: true, dataIndex: 'ship_from', hidden: true},
                    {
                        header: 'Invoice', sortable: true, dataIndex: 'invoice_number',
                        editor: new Ext.form.TextField({
                            allowBlank: false,
                            readOnly:true
                        })
                    },
                    {
                        header: 'Tracking', dataIndex: 'tracking_code',
                        editor: new Ext.form.TextField({
                            allowBlank: false,
                            readOnly:true
                        })
                    },
                    // instead of specifying renderer: Ext.util.Format.dateRenderer('m/d/Y') use xtype
                    { header: 'Date', dataIndex: 'created_at', xtype: 'datecolumn', format: 'M d, Y' },
                    { header: 'Time', dataIndex: 'created_at', xtype: 'datecolumn', format: 'H:m:s'}
                ]
            });



            Monogram.Shipment.GridPanel.superclass.initComponent.call(this);

            // self.getSelectionModel().on('rowselect', self.onRowSelect, this);
            // self.getSelectionModel().on('rowdeselect', self.onRowDeSelect, this);
        },



        configureStore: function() {
            let self = this;
            return self.store||(
                new Ext.data.JsonStore({
                    proxy: new Ext.data.HttpProxy({
                        method: 'GET',
                        url:'/usps/manifest/shipments'
                    }),
                    idProperty: 'id',
                    root: 'shipments',
                    fields: ['id', 'ship_id', 'ship_from',{name: 'invoice_number', type: 'string'}, 'tracking_code', 'created_at']
                })
            );
        },

        configureToolBar: function() {
            let self = this;
            return self.tbar||([
                {
                    xtype: 'button',
                    iconCls: 'view-refresh-icon',
                    text: 'Refresh',
                    handler: function(){self.getStore().load({
                        params: {location: self.locationValue}
                    })}
                },
                '|',
                {
                    xtype: 'button',
                    iconCls: 'view-task-icon',
                    text: 'Create Manifest',
                    handler: self.onCreateManifestClick,
                    scope: this
                },
                {
                    xtype: 'button',
                    iconCls: 'view-list-details-icon',
                    text: 'Shipment Details'
                },
                '->',
                {
                    xtype: 'button',
                    iconCls: 'application-pdf-icon',
                    text: 'PDF Label'
                },
                {
                    xtype: 'button',
                    iconCls: 'go-down-search-icon',
                    text: 'Re-download Label',
                    handler: self.onRedownloadLabelClick,
                    scope: this
                }
            ]);
        },

        onCreateManifestClick: function(button, event) {
            let self = this;
            let records = self.getSelectionModel().getSelections();
            let shipments = [];
            Ext.each(records, function(item) {
                shipments.push(item.data.ship_id);
            }, this);

            if(records.length) {
                self.showMask('Creating Manifest...');
                Ext.Ajax.request({
                    callback:function (options, success, response)
                    {
                        self.hideMask();
                        if (success == true)
                        {
                            var decoded = Ext.decode(response.responseText);
                            if (decoded.success == false)
                            {
                                Ext.MessageBox.alert('Error', decoded.msg);
                            }
                        }
                        else
                        {
                            Ext.MessageBox.alert('Error', 'XHR Request failed!');
                        }
                        self.getStore().reload();

                    },
                    params:{
                        shipments: Ext.encode(shipments),
                        location: self.locationValue,
                        _token: "{{ csrf_token() }}"
                    },
                    method: 'POST',
                    scope:this,
                    url:'/usps/manifest/shipments/create_manifest'
                });
            } else {
                Ext.MessageBox.alert('Error', 'No records selected.');
            }
        },

        onRedownloadLabelClick: function(button, event) {
            let record = this.getSelectionModel().getSelected();
            let self = this;
            if (!record || record.phantom === true) {
                return;
            }

            self.showMask('Downloading ZPL Label'  + '...');
            Ext.Ajax.request({
                callback:function (options, success, response)
                {
                    self.hideMask();
                    if (success)
                    {
                        var decoded = Ext.decode(response.responseText);
                        if (decoded.success === false)
                        {
                            Ext.MessageBox.alert('Error', 'Server Error!');
                        }
                    }
                    else
                    {
                        Ext.MessageBox.alert('Error', 'XHR Request failed!');
                    }
                },
                params:{
                    ship_id:record.data.ship_id,
                    invoice_number: record.data.invoice_number
                },
                method: 'GET',
                scope:this,
                url:'/usps/manifest/shipments/redownloadlabel'
            });
        },

        showMask: function (msg)
        {
            this.body.mask(msg, 'x-mask-loading');
        },

        hideMask: function ()
        {
            this.body.unmask();
        }
    });

    Monogram.Shipment.Manifest.GridPanel = Ext.extend(Ext.grid.EditorGridPanel, {
        stripeRows: true,
        loadMask: true,
        viewConfig: {
            forceFit: true
        },

        listeners: {
            'afterrender': function(grid) {
                grid.getStore().load();
            }
        },

        initComponent: function()
        {
            let self = this;
            self.store = self.configureStore();
            self.tbar = self.configureToolBar();
            self.sm = new Ext.grid.CheckboxSelectionModel({singleSelect:true}),
                self.cm = new Ext.grid.ColumnModel({
                    defaults: {
                        width: 120,
                        sortable: true
                    },
                    columns: [
                        self.sm,
                        {id: 'id', header: 'Id', sortable: true, dataIndex: 'id', hidden: true},
                        {id: 'ship_from', header: 'Shipped From', sortable: true, dataIndex: 'ship_from'},
                        {id: 'num_shipments', header: 'Shipments', sortable: true, dataIndex: 'num_shipments'},
                        {
                            header: 'ScanForm Status', sortable: true, dataIndex: 'sf_status',
                        },
                        {
                            header: 'Batch Status', dataIndex: 'batch_status',
                        },
                        // instead of specifying renderer: Ext.util.Format.dateRenderer('m/d/Y') use xtype
                        { header: 'Date', dataIndex: 'created_at', xtype: 'datecolumn', format: 'M d, Y' },
                        { header: 'Time', dataIndex: 'created_at', xtype: 'datecolumn', format: 'H:m:s'}
                    ]
                });



            Monogram.Shipment.Manifest.GridPanel.superclass.initComponent.call(this);

            self.getSelectionModel().on('rowselect', function(){Ext.getCmp('manifest-download').enable();}, this);
            self.getSelectionModel().on('rowdeselect', function(){Ext.getCmp('manifest-download').disable();}, this);
        },



        configureStore: function() {
            let self = this;
            return self.store||(
                new Ext.data.JsonStore({
                    proxy: new Ext.data.HttpProxy({
                        method: 'GET',
                        url:'/usps/manifest/batches'
                    }),
                    idProperty: 'id',
                    root: 'batches',
                    fields: ['id', 'ship_from', 'num_shipments', 'batch_status', 'sf_status', 'form_url', 'created_at']
                })
            );
        },

        configureToolBar: function() {
            let self = this;
            return self.tbar||([
                {
                    xtype: 'button',
                    iconCls: 'view-refresh-icon',
                    text: 'Refresh',
                    handler: function(){self.getStore().reload();}
                },
                '->',
                {
                    xtype: 'button',
                    id: 'manifest-download',
                    iconCls: 'application-pdf-icon',
                    text: 'PDF Label',
                    disabled:true,
                    handler: function(){
                        let self = this;
                        let record = self.getSelectionModel().getSelected();
                        if(record.data.form_url == null) {
                            Ext.MessageBox.alert('Error', 'Scan Form not found.');
                        } else{
                            window.open(record.data.form_url, '_blank');
                        }

                    },
                    scope: this
                }
            ]);
        },

        showMask: function (msg)
        {
            this.body.mask(msg, 'x-mask-loading');
        },

        hideMask: function ()
        {
            this.body.unmask();
        }
    });

    Ext.onReady(function(){
        Ext.QuickTips.init();

        let shipmentGridFilter = new Ext.ux.grid.GridFilters({
            // encode and local configuration options defined previously for easier reuse
            encode: false, // json encode the filter query
            local: true,   // defaults to false (remote filtering)
            filters: [
                {
                    type: 'string',
                    dataIndex: 'tracking_code'
                },
                {
                    type: 'string',
                    dataIndex: 'invoice_number'
                }
            ]
        });

        // second tabs built from JS
        let tabPanel = new Ext.TabPanel({
            activeTab: 0,
            width:800,
            height:600,
            plain:true,
            defaults:{autoScroll: true},
            items:[
                new Monogram.Shipment.GridPanel({
                    title: 'NY Shipments', locationValue: 'NY',
                    plugins: [
                        shipmentGridFilter
                    ]
                }),
                new Monogram.Shipment.GridPanel({
                    title: 'FL Shipments', locationValue: 'FL',
                    plugins: [
                        shipmentGridFilter
                    ]
                }),
                new Monogram.Shipment.Manifest.GridPanel({
                    title: 'Batches/Manifest',
                    plugins: [
                        shipmentGridFilter
                    ]
                }),
            ]
        });

        var Panel = new Ext.Panel({
            renderTo: 'main-panel',
            title: 'USPS Driver Manifest',
            items: [
                tabPanel
            ]
        });

        function handleActivate(tab){
            alert(tab.title + ' was activated.');
        }
    });
</script>
</body>
</html>