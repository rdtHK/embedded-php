

## TODO

- [ ] Cache
    - [ ] InMemoryCache
    - [ ] FilesystemCache
- [ ] Other loaders (+ tests)
    - [ ] File loader (list of directories to load from)
- [ ] Catch errors
    - [ ] + Better exceptions
- [ ] Escape <?php & ?>
- [ ] Do something about %> inside strings
- [ ] Global parameters
    - [ ] EmbeddedPHP->param($name, $value);
- [x] A way of including templates (add an ephp variable?)
- [ ] A way of extending templates
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


- [ ] Maybe change it to return a string instead of printing directly?
    - [ ] Maybe both? render & print methods