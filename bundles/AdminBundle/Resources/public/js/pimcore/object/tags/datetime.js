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

pimcore.registerNS("pimcore.object.tags.datetime");
pimcore.object.tags.datetime = Class.create(pimcore.object.tags.abstract, {

    type:"datetime",

    initialize:function (data, fieldConfig) {
        this.data = data;
        this.fieldConfig = fieldConfig;
    },

    applyDefaultValue: function() {
        if ((typeof this.data === "undefined" || this.data === null) && this.fieldConfig.defaultValue) {
            this.defaultValue = pimcore.helpers.date.format.noTimezone(this.fieldConfig.defaultValue);
        } else if ((typeof this.data === "undefined" || this.data === null) && this.fieldConfig.useCurrentDate) {
            this.defaultValue = pimcore.helpers.date.format.noTimezone(new Date());
        }

        if (this.defaultValue) {
            this.data = this.defaultValue;
        }
    },

    getGridColumnConfig:function (field) {
        return {
            text: t(field.label),
            width:150,
            sortable:true,
            dataIndex:field.key,
            getEditor:this.getWindowCellEditor.bind(this, field),
            renderer:function (key, value, metaData, record) {
                        this.applyPermissionStyle(key, value, metaData, record);

                        if (record.data.inheritedFields && record.data.inheritedFields[key] && record.data.inheritedFields[key].inherited == true) {
                            metaData.tdCls += " grid_value_inherited";
                        }

                        if (value) {
                            return pimcore.helpers.date.format.noTimezone(value, true);
                        }
                        return "";
                    }.bind(this, field.key)};
    },

    getGridColumnFilter:function (field) {
        return {type:'date', dataIndex:field.key, dateFormat: 'm/d/Y'};
    },

    getLayoutEdit:function () {

        var date = {
            width:130,
            format: "Y-m-d"
        };

        var time = {
            format:"H:i",
            emptyText:"",
            width:90
        };

        if (this.data) {
            date.value = time.value = pimcore.helpers.date.parse(this.data);
        }

        this.datefield = Ext.create('Ext.form.field.Date', date);
        this.timefield = Ext.create('Ext.form.field.Time', time);

        var componentItems = [this.datefield, this.timefield];
        if (this.shouldDisplayTimezone()) {
            componentItems.push({
                xtype: 'panel',
                style: 'margin-top: 10px; margin-left: 10px;',
                html: Ext.util.Format.htmlEncode(t(pimcore.settings.timezone_info.name))
            });
        }

        var componentCfg = {
            layout: 'hbox',
            fieldLabel:this.fieldConfig.title,
            combineErrors:false,
            items:componentItems,
            componentCls: this.getWrapperClassNames(),
            isDirty: function() {
                return this.datefield.isDirty() || this.timefield.isDirty()
            }.bind(this)
        };

        if (this.fieldConfig.labelWidth) {
            componentCfg.labelWidth = this.fieldConfig.labelWidth;
        }

        if (this.fieldConfig.labelAlign) {
            componentCfg.labelAlign = this.fieldConfig.labelAlign;
        }

        this.component = Ext.create('Ext.form.FieldContainer', componentCfg);

        return this.component;
    },

    shouldDisplayTimezone: function() {
        if (this.fieldConfig.showTimezone === 'always') {
            return true;
        }
        if (this.fieldConfig.showTimezone === 'when_differs') {
            return !pimcore.helpers.date.isBrowserInSameTimezoneAsServer();
        }
        return false;
    },

    getLayoutShow:function () {

        this.component = this.getLayoutEdit();

        this.component.addCls('x-form-readonly');
        this.datefield.setReadOnly(true);
        this.timefield.setReadOnly(true);

        return this.component;
    },

    getValue:function () {

        if (this.datefield.getValue()) {
            var value = this.datefield.getValue();
            var dateString = Ext.Date.format(value, "Y-m-d");

            if (this.timefield.getValue()) {
                var timeValue = this.timefield.getValue();
                timeValue = Ext.Date.format(timeValue, "H:i");
                dateString += " " +  timeValue;
            }
            else {
                dateString += " 00:00";
            }

            value = Ext.Date.parseDate(dateString, "Y-m-d H:i");
            if (value && typeof value.getTime == "function") {
                return pimcore.helpers.date.format.noTimezone(value);
            }

            return value;
        }
        return false;
    },

    getName:function () {
        return this.fieldConfig.name;
    },

    isDirty:function () {
        var dirty = false;

        if(this.defaultValue) {
            return true;
        }

        if (this.component && typeof this.component.isDirty == "function") {
            if (this.component.rendered) {
                dirty = this.component.isDirty();

                // once a field is dirty it should be always dirty (not an ExtJS behavior)
                if (this.component["__pimcore_dirty"]) {
                    dirty = true;
                }
                if (dirty) {
                    this.component["__pimcore_dirty"] = true;
                }

                return dirty;
            }
        }

        return false;
    },

    getCellEditValue: function () {
        return this.getValue();
    }

});
