
<script>


    let icon = {
        success:
            '<i class="bi bi-patch-check"></i>',
        danger:
            '<i class="bi bi-person"></i>',
        warning:
            '<i class="bi bi-person"></i>',
        info:
            '<i class="bi bi-patch-check"></i>',
    };
    const showToast = (
        message = "Sample Message",
        toastType = "info",
        duration = 5000) => {
        if ( !Object.keys(icon).includes(toastType))
            toastType = "info";

        let box = document.createElement("div");
        box.classList.add("toast", `toast-${toastType}`);

        box.innerHTML = ` <div class="toast-content-wrapper">
                      <div class="toast-icon">
                        ${icon[toastType]}
                      </div>
                      <div class="toast-message">${message}</div>
                      <div class="toast-progress"></div>
                      </div>`;

        duration = duration || 5000;

        box.querySelector(".toast-progress").style.animationDuration =`${duration / 1000}s`;

        let toastAlready = document.body.querySelector(".toast");
        if (toastAlready) {
            toastAlready.remove();
        }

        document.body.appendChild(box)
    }

        @foreach (session('flash_notification', collect())->toArray() as $message)
            showToast( '{!! $message['message'] !!}','{{ $message['level'] }}',10000)

        @endforeach





</script>

