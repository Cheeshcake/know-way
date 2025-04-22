document.addEventListener("DOMContentLoaded", function () {
  const termsLink = document.querySelector(".terms a");
  const modal = document.getElementById("terms-modal");
  const closeBtn = document.querySelector(".modal-close");
  const agreeBtn = document.querySelector(".modal-agree");

  termsLink.addEventListener("click", function (e) {
    e.preventDefault();
    modal.classList.add("active");
  });

  closeBtn.addEventListener("click", function () {
    modal.classList.remove("active");
  });

  agreeBtn.addEventListener("click", function () {
    modal.classList.remove("active");
    document.getElementById("terms").checked = true;
  });

  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.classList.remove("active");
    }
  });
});
