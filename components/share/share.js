document.addEventListener('DOMContentLoaded', function () {

  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.like-btn');
    if (!btn) return;

    const postId = btn.dataset.postId;
    const countEl = btn.querySelector('.like-count');
    const icon = btn.querySelector('.like-icon');

    if (!postId || !countEl || !icon) return;

    const storageKey = 'liked_post_' + postId;
    const isLiked = localStorage.getItem(storageKey) === 'true';
    const actionType = isLiked ? 'unlike' : 'like';

    fetch(aptoxLike.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'toggle_post_like',
        post_id: postId,
        action_type: actionType,
        nonce: aptoxLike.nonce
      })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;

      if (actionType === 'like') {
        icon.src = aptoxLike.iconFilled;
        localStorage.setItem(storageKey, 'true');
      } else {
        icon.src = aptoxLike.iconOutline;
        localStorage.removeItem(storageKey);
      }

      countEl.textContent = data.data.likes;
    });
  });

  document.querySelectorAll('.like-btn').forEach(function (btn) {
    const postId = btn.dataset.postId;
    const icon = btn.querySelector('.like-icon');
    if (!postId || !icon) return;

    const storageKey = 'liked_post_' + postId;
    if (localStorage.getItem(storageKey) === 'true') {
      icon.src = aptoxLike.iconFilled;
    }
  });

});
