

## TODO

- [ ] Maybe change render to return a string instead of printing directly?
    - [ ] Maybe both? render & print methods
- [ ] Cache
    - [ ] InMemoryCache
    - [ ] FilesystemCache
- [x] Escape <? & ?>
- [ ] Do something about %> inside strings
- [ ] A way of extending templates

    '''
    function layout($code)
    {
        $php = 'return function () {';
        $php = $this->compile($code);
        $php = '}';
        return eval($php)();
    }


    foreach (layout($globals) as $content) {
        $c = $content??'content';
        call_user_func($scope, $php, array_merge($p, ['__CONTENT_BLOCK__' => $c]);
    }

    function content($name)
    {
        global $__CONTENT_BLOCK__;
        return strcasecmp($name, $__CONTENT_BLOCK__);
    }

    <% if content('head'): %>
    <% endif; %>

    <% if content(): %>
    <% endif; %>
    '''

