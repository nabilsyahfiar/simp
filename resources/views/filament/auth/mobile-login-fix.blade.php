<style>
    .fi-simple-header {
        text-align: center;
        align-items: center;
    }

    .fi-simple-header .fi-logo {
        justify-content: center;
        text-align: center;
        width: 100%;
        font-size: 1.75rem;
        line-height: 1.2;
        max-height: none;
    }

    .fi-simple-header-subheading {
        font-size: 0.9rem;
        font-weight: 500;
        color: rgb(156 163 175);
    }

    @media (max-width: 640px) {
        .fi-simple-main-ctn {
            align-items: center;
            justify-content: center;
        }

        .fi-simple-main {
            width: calc(100vw - 1.5rem);
            max-width: calc(100vw - 1.5rem);
            margin: 1rem 0;
            padding: 1.25rem 1rem;
            border-radius: 0.75rem;
            box-sizing: border-box;
        }

        .fi-simple-page-content {
            gap: 1rem;
        }

        .fi-simple-header .fi-logo {
            margin-bottom: 0.25rem;
            max-height: none;
            overflow: visible;
            white-space: normal;
            line-height: 1.2;
            font-size: 1.875rem;
            text-wrap: balance;
            justify-content: center;
            text-align: center;
            width: 100%;
        }

        .fi-simple-header .fi-logo img,
        .fi-simple-header .fi-logo svg {
            max-height: 2rem;
            width: auto;
        }
    }
</style>
