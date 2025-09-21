<head>
    <style>
.post-card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    background: #ffffff;
}
.comments-section {
    border-top: 1px solid #f3f4f6;
    background: #ffffff;
}

.comment-item {
    transition: background-color 0.2s ease;
}
    </style>
</head>

<script>
    //post

function createPostElement(post) {
    const postDiv = document.createElement("div");
    postDiv.className = "post-card post-item border border-gray-200 rounded-lg p-3 mb-3 bg-white shadow-sm";
    postDiv.setAttribute("data-post-id", post.id);

            // Render initial comments
            const commentsPerPage = 2;
            let commentsHTML = '';
            
            if (post.comments && post.comments.length > 0) {
                const visible = post.comments.slice(0, commentsPerPage);
                const remaining = post.comments.slice(commentsPerPage);

                commentsHTML += `
                    <div class="pt-2 border-t comments-section">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500">
                                ${post.comments.length} ${post.comments.length === 1 ? 'Comment' : 'Comments'}
                            </span>
                        </div>
                        <ul class="space-y-1 comments-list" data-post-id="${post.id}" id="comments-list-${post.id}">
                            ${visible.map(c => renderComment(c, post.id)).join('')}
                        </ul>
                `;

                if (remaining.length > 0) {
                    commentsHTML += `
                        <button type="button" class="mt-1 text-sm text-blue-600 transition-colors hover:text-blue-800 hover:underline load-more-comments-btn"
                            data-post-id="${post.id}" id="comment-toggle-${post.id}">
                            Show ${Math.min(2, remaining.length)} more comments
                        </button>
                    `;
                    // Store remaining comments for pagination
                    window._commentChunks = window._commentChunks || {};
                    window._commentChunks[post.id] = { comments: remaining, index: 0 };
                }

                commentsHTML += `</div>`;
            } else {
                commentsHTML = `
                    <div class="pt-2 border-t comments-section">
                        <span class="text-xs italic text-gray-400">No comments yet</span>
                    </div>
                `;
            }

            // Comment form
            const csrfToken = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            commentsHTML += `
                <form method="POST" action="/posts/${post.id}/comments" class="mt-2 comment-form" data-post-id="${post.id}">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <textarea name="body" rows="2" class="w-full p-1 text-sm border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required placeholder="Write a comment..."></textarea>
                    <button type="submit" class="px-2 py-1 mt-1 text-xs text-white transition-colors bg-blue-500 rounded hover:bg-blue-600">Comment</button>
                </form>
            `;

            // Post body logic
    const full = post.body || '';
    const short = full.length > 300 ? full.substring(0, 300) + '...' : full;
    const isLong = full.length > 300;
    const postBody = isLong
        ? `
            <p class="hidden mb-3 text-sm leading-relaxed text-gray-700 dont-delete-this-is-a-workaround">
                <span class="post-body" data-full="${full}" data-short="${short}">${short}</span>
                <button class="ml-1 text-xs text-blue-600 toggle-post-body hover:underline">Show more</button>
            </p>
            <p class="mb-3 text-sm leading-relaxed text-gray-700">
                <span class="post-body" data-full="${full}" data-short="${short}">${short}</span>
                <button class="ml-1 text-xs text-blue-600 toggle-post-body hover:underline">Show more</button>
            </p>
        `
        : `<p class="mb-3 text-sm leading-relaxed text-gray-700">${full}</p>`;

    // Build the complete post HTML
    postDiv.innerHTML = `
        <div class="flex items-start justify-between mb-2">
            <h4 class="text-sm font-semibold text-gray-800">${post.title || 'Untitled'}</h4>
            <span class="text-xs text-gray-500">
                ${new Date(post.created_at).toLocaleString(undefined, {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                })}
            </span>
        </div>

        ${post.image ? `
            <div class="mb-3">
                <img src="${post.image.startsWith('/storage/') ? post.image : `/storage/${post.image}`}" 
                    alt="Post image" 
                    class="object-cover w-full mt-2 rounded-md">
            </div>
        ` : ''}

    <div class="flex items-center mb-2 space-x-2 text-xs">
        <span class="text-gray-400">(UID: ${post.user?.id ?? 'N/A'})</span>
        <span class="font-medium text-gray-700">${post.user?.name ?? 'Unknown'}</span>
        ${post.is_owner ? '<span class="font-medium text-yellow-600">(You)</span>' : ''}
${(authUserRole === 'admin' || authUserRole === 'staff') ? `

    <button 
        class="toggle-moderation-btn ml-2 px-2 py-1 text-xs font-semibold 
            ${post.moderated ? 'text-red-700 bg-white hover:bg-gray-100' : 'text-yellow-700 bg-yellow-100 bg-white hover:bg-yellow-200'}
            border rounded transition-colors"
        data-post-id="${post.id}" 
        data-moderated="${post.moderated}"
        title="${post.moderated ? 'Click to revoke' : 'Click to approve'}"
    >
        ${post.moderated ? '⚠️ Revoke approval' : '⚠️ Pending approval'}
    </button>

` : ''}
    </div>

                ${postBody}
<div data-post-id="${post.id}" class="flex items-center justify-between pt-2 mb-2 border-t">
    <div class="flex items-center space-x-2">
        <button 
            class="like-btn flex items-center space-x-1 px-2 py-1 rounded hover:bg-gray-100 transition-colors ${post.is_liked ? 'text-red-500 liked' : 'text-gray-500'}" 
            data-post-id="${post.id}"
            title="${post.is_liked ? 'Unlike' : 'Like'} this post"
        >
            <span class="heart-icon">${post.is_liked ? '❤️' : '🤍'}</span>
            <span class="text-xs">Like</span>
        </button>
        <div class="flex items-center space-x-1 text-xs">
            <span class="font-medium text-gray-600 likes-count">${post.likes_count || 0}</span>
            <span class="text-gray-500 likes-text">${(post.likes_count || 0) === 1 ? 'like' : 'likes'}</span>
        </div>

        ${post.is_owner ? `
            <a href="/posts/${post.id}/edit" 
            class="flex items-center px-2 py-1 space-x-1 text-yellow-600 transition-colors rounded like-btn hover:bg-gray-100 edit-post"
            title="Edit this post">
                <span>✏️</span>
                <span class="text-xs">Edit</span>
            </a>
        ` : ''}

        ${(post.is_owner || authUserRole === 'admin' || authUserRole === 'staff') ? `
            <button 
                class="flex items-center px-2 py-1 space-x-1 text-xs text-red-500 transition-colors rounded like-btn delete-post-btn hover:bg-gray-100"
                data-post-id="${post.id}"
                title="Delete this post"
            >
                <span>🗑️</span>
                <span>Delete</span>
            </button>
        ` : ''}
    </div>
</div>

                ${commentsHTML}
            `;

            // Event listeners
            setupPostEventListeners(postDiv, post.id);
            
            return postDiv;
        }

// Toggle comment bodies
postDiv.querySelectorAll('.toggle-comment').forEach(btn => {
    const commentSpan = btn.previousElementSibling;
    if (commentSpan && commentSpan.dataset.full && commentSpan.dataset.short) {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const showingFull = commentSpan.textContent === commentSpan.dataset.full;
            commentSpan.textContent = showingFull ? commentSpan.dataset.short : commentSpan.dataset.full;
            btn.textContent = showingFull ? 'Show more' : 'Show less';
        });
    }
});


// Load more comments
const loadMoreBtn = postDiv.querySelector('.load-more-comments-btn');
if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', (e) => {
        e.preventDefault();
        loadMoreCommentsForPost(postId);
    });
}

function loadMoreCommentsForPost(postId) {
    const chunk = window._commentChunks?.[postId];
    if (!chunk) return;

    const container = document.getElementById(`comments-list-${postId}`);
    const btn = document.getElementById(`comment-toggle-${postId}`);

    if (!container || !btn) return;

    const count = 2;
    const start = chunk.index;
    const end = start + count;
    const currentChunk = chunk.comments.slice(start, end);

    // add comment
    currentChunk.forEach(c => {
        container.insertAdjacentHTML('beforeend', renderComment(c, postId));
    });

    // comment body fold
    container.querySelectorAll('.toggle-comment').forEach(toggleBtn => {
        if (!toggleBtn.hasAttribute('data-listener-added')) {
            const commentSpan = toggleBtn.previousElementSibling;
            if (commentSpan && commentSpan.dataset.full && commentSpan.dataset.short) {
                toggleBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const showingFull = commentSpan.textContent === commentSpan.dataset.full;
                    commentSpan.textContent = showingFull ? commentSpan.dataset.short : commentSpan.dataset.full;
                    toggleBtn.textContent = showingFull ? 'Show more' : 'Show less';
                });
                toggleBtn.setAttribute('data-listener-added', 'true');
            }
        }
    });

    chunk.index = end;

    // comment fold
    if (chunk.index >= chunk.comments.length) {
        btn.remove();
    } else {
        const remaining = chunk.comments.length - chunk.index;
        btn.textContent = `Show ${Math.min(count, remaining)} more comments`;
    }
}