/*
        jQuery.fn.log - taken from Jabbering Giraffe
        -- added code to deal with "IE blowing chunks on console.log not existing"
*/

/*
        If your browser does not have or support or have firebug installed, this script will stop any errors from being triggered upon encountering a
        console.log function or anything else under the console object.

        Thanks firebug team!
        http://ryan.ifudown.com/ - Ryan Rampersad
*/
if (!window.console || !console.firebug)
{
    var names = [
        "log",
                "debug",
                "info",
                "warn",
                "error",
                "assert",
                "dir",
                "dirxml",
                "group",
                "groupEnd",
                "time",
                "timeEnd",
                "count",
                "trace",
                "profile",
                "profileEnd"
        ];

    window.console = {};

    for (var i=0; i<names.length; ++i)
    {
        window.console[names[i]] = function() {};
    }

    names = null;
}

/*
        this can be put directly in a selector chain
        e.g.
        $(root).find('li.source > input:checkbox').log("sources to uncheck").removeAttr("checked");
*/
jQuery.fn.dump = function (msg)
{
  console.log("%s: %o", msg || '', this);
  return this;
};

jQuery.log = function ()
{
  console.log(Array.prototype.slice.apply(arguments).join(', '));
};
