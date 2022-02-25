/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

pimcore.registerNS("pimcore.asset.metadata.tags.date");
pimcore.asset.metadata.tags.date = Class.create(pimcore.asset.metadata.tags.abstract, {

    type:"date",

    initialize:function (data, fieldConfig) {

        this.data = null;

        if (typeof data !== "undefined" && data !== null) {
            this.data = data;
        } else if (fieldConfig.useCurrentDate) {
            this.data = pimcore.helpers.date.format.dateOnly(new Date());
        }

        this.fieldConfig = fieldConfig;
    },

    getGridColumnConfig:function (field) {
        return {
            text: field.label,
            width: this.getColumnWidth(field, 120),
            sortable:false,
            dataIndex:field.key,
            getEditor: this.getWindowCellEditor.bind(this, field),
            filter: this.getGridColumnFilter(field)
        };
    },

    getGridColumnFilter:function (field) {
        return {type:'date', dataIndex:field.key, dateFormat: 'm/d/Y'};
    },

    getLayoutEdit:function () {

        var date = {
            fieldLabel:this.fieldConfig.title,
            name:this.fieldConfig.name,
            componentCls:"object_field",
            width:130,
            format: "Y-m-d"
        };

        if (this.fieldConfig.labelWidth) {
            date.labelWidth = this.fieldConfig.labelWidth;
        }
        date.width += date.labelWidth;

        if (this.data) {
            date.value = pimcore.helpers.date.parse(this.data);
        }

        this.component = new Ext.form.DateField(date);
        return this.component;
    },

    getValue:function () {
        var value = this.component.getValue();
        if (value) {
            return pimcore.helpers.date.format.dateOnly(value);
        }
        return false;
    },

    getCellEditValue: function () {
        return this.getValue();
    },

    getName:function () {
        return this.fieldConfig.name;
    },

    getGridCellEditor: function (gridtype, record) {
        return Ext.create('Ext.form.field.Date', {
            format: "Y-m-d"
        });
    },

    convertPredefinedGridData: function(v, r) {
        if (v && !(v instanceof Date)) {
            return pimcore.helpers.date.parse(v);
        }
        return v;
    },

    getGridCellRenderer: function(value, metaData, record, rowIndex, colIndex, store) {
        if (value) {
            return pimcore.helpers.date.format.dateOnly(value);
        }

        return Ext.util.Format.htmlEncode(value);
    },

    marshal: function(value) {
        // value used for submission
        if (value) {
            value = pimcore.helpers.date.format.dateOnly(value)
        }

        return value;
    },

    unmarshal: function(value) {
        // process received and transform it to grid value
        if (value) {
            value = pimcore.helpers.date.parse(value)
        }

        return value;
    },
});
