{% if {{ is_action }} && {{ has_return }} : %}
            $this->assertSame($expected, do_action('{{ event }}', {{ parameters }}));
{% else %}
            do_action('{{ event }}', {{ parameters }});
{% endif %}
{% if ! {{ is_action }} && {{ has_return }} : %}
            $this->assertSame($expected, apply_filters('{{ event }}', {{ parameters }}));
{% else %}
            apply_filters('{{ event }}', {{ parameters }});
{% endif %}