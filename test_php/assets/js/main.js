
document.addEventListener("DOMContentLoaded", function () {

    // Xác nhận trước khi xóa sách
    let removeButtons = document.querySelectorAll(".remove");

    removeButtons.forEach(function (button) {

        button.addEventListener("click", function (event) {

            let confirmDelete = confirm(
                "Bạn có chắc chắn muốn xóa không?"
            );

            if (!confirmDelete) {

                event.preventDefault();

            }

        });

    });

});
