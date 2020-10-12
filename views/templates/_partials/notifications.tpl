<aside id="notifications">
  {if strpos($url_post, 'success') !== false}

    {block name='notifications_info'}
      <article class="notification notification-info" role="alert" data-alert="info">
        <ul>
            <li>C'est ok</li>
        </ul>
      </article>
    {/block}
  {/if}

</aside>