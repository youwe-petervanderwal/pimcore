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

// some global helper functions
pimcore.registerNS("pimcore.helpers.date");
pimcore.helpers.date = (function () {
    const iso8601 = {
        dateOnly: "Y-m-d",
        noTimezone: "Y-m-d\\TH:i:s"
    };

    /**
     * @param {string|Date|null} value
     * @return {Date|null}
     */
    const parse = function (value) {
        // Note: (value instanceof Date) returns false in some situations, I don't like this odd comparison but at least it works
        if (Object.prototype.toString.call(value) === "[object Date]") {
            return value;
        }

        switch (value && value.length ? value.length : 0) {
            case 10:
                return Ext.Date.parse(value, iso8601.dateOnly);
            case 19:
                if (value.substr(10, 1) === " ") {
                    // Rewrite "Y-m-d H:i:s" to "Y-m-d\TH:i:s"
                    value = value.substr(0, 10) + "T" + value.substr(11);
                }
                return Ext.Date.parse(value, iso8601.noTimezone);
            default:
                return null;
        }
    }

    /**
     * @param {string|Date|null} value
     * @param {string} format
     * @return {string|null}
     */
    const format = function (value, format) {
        let dateObject = parse(value);
        if (!dateObject) {
            return null;
        }
        return Ext.Date.format(dateObject, format);
    };

    const formatters = {
        /**
         * @param {string|Date|null} value
         * @return {string|null}
         */
        dateOnly: function (value) {
            return format(value, iso8601.dateOnly);
        },

        /**
         * @param {string|Date|null} value
         * @param {boolean} pretty
         * @return {string|null}
         */
        noTimezone: function (value, pretty) {
            return format(value, pretty ? "Y-m-d H:i" : iso8601.noTimezone);
        },

        database: function (value) {
            return format(value, "Y-m-d H:i:s")
        }
    };

    const isBrowserInSameTimezoneAsServer = function () {
        // Don't compare on the name of the timezone as "Europe/Vienna" differs in name from "Europe/Amsterdam" but are
        // actually the same zone (both CET/CEST)

        let time = new Date(pimcore.settings.timezone_info.current_timestamp * 1000);
        let browserOffset = time.getTimezoneOffset() * -60;
        if (browserOffset != pimcore.settings.timezone_info.current_offset_seconds) {
            return false;
        }

        let browserUsesDst = Ext.Date.isDST(time) || Ext.Date.isDST(Ext.Date.add(time, Ext.Date.MONTH, 6));
        if (browserUsesDst != pimcore.settings.timezone_info.uses_dst) {
            return false;
        }

        return true;
    };

    return {
        parse: parse,
        format: formatters,
        isBrowserInSameTimezoneAsServer: isBrowserInSameTimezoneAsServer
    }
}());