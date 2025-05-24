<style>
    .floating-button {
        position: fixed;
        bottom: 70px;
        right: 20px;
        z-index: 1000;
        color: white;
        border-radius: 50% !important;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .floating-button:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
</style>
<button class="btn btn-primary floating-button bg-indigo-dark " type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottom" aria-controls="offcanvasBottom"><i class="fa-solid fa-chalkboard-user"></i></button>