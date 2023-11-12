<!DOCTYPE html>
<html>

<head>
    <title>Verification Email</title>
    <style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
    }

    .gjs-heading {
        margin: 0;
        color: rgba(29, 40, 55, 1);
    }

    .gjs-grid-column {
        flex: 1 1 0%;
        padding: 5px 0;
    }

    .gjs-grid-row {
        display: flex;
        justify-content: flex-start;
        align-items: stretch;
        flex-direction: row;
        min-height: auto;
        padding: 10px 0;
    }

    #iau9 {
        font-family: Arial, Helvetica, sans-serif;
        color: #475569;
    }

    .gjs-grid-column.feature-item {
        padding-top: 15px;
        padding-right: 15px;
        padding-bottom: 15px;
        padding-left: 15px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        min-width: 30%;
    }

    .gjs-grid-column.testimonial-item {
        padding-top: 15px;
        padding-right: 15px;
        padding-bottom: 15px;
        padding-left: 15px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        min-width: 45%;
        background-color: rgba(247, 247, 247, 0.23);
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
        border-bottom-left-radius: 5px;
        align-items: flex-start;
        border-top-width: 1px;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-left-width: 1px;
        border-top-style: solid;
        border-right-style: solid;
        border-bottom-style: solid;
        border-left-style: solid;
        border-top-color: rgba(0, 0, 0, 0.06);
        border-right-color: rgba(0, 0, 0, 0.06);
        border-bottom-color: rgba(0, 0, 0, 0.06);
        border-left-color: rgba(0, 0, 0, 0.06);
    }

    .gjs-text-blue {
        color: rgb(36, 99, 235);
    }

    #ik2rdi {
        max-width: 1200px;
        align-items: center;
        display: flex;
        flex-direction: column;
        padding-top: 50px;
        padding-right: 50px;
        padding-bottom: 50px;
        padding-left: 62px;
        border-top-left-radius: 50px;
        border-top-right-radius: 50px;
        border-bottom-right-radius: 50px;
        border-bottom-left-radius: 50px;
        border-top-width: 1px;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-left-width: 1px;
        border-top-style: solid;
        border-right-style: solid;
        border-bottom-style: solid;
        border-left-style: solid;
        border-top-color: rgba(0, 0, 0, 0.06);
        border-right-color: rgba(0, 0, 0, 0.06);
        border-bottom-color: rgba(0, 0, 0, 0.06);
        border-left-color: rgba(0, 0, 0, 0.06);
        background-image: radial-gradient(515px at 50% 141%, rgba(35, 98, 235, 0.22), white 51%);
        background-position: 0px 0px;
        background-size: 100% 100%;
        background-repeat: repeat;
        background-attachment: scroll;
        background-origin: padding-box;
        box-shadow: 0px 10px 15px 0 rgba(0, 0, 0, 0.07);
        transform-style: flat;
    }

    #i83vu9 {
        font-size: 2.5rem;
        text-align: center;
    }

    #ir465 {
        justify-content: center;
    }

    @media (max-width:992px) {
        .gjs-grid-row {
            flex-direction: column;
        }
    }
    </style>
</head>

<body id="iau9">
    <div id="ir465" class="gjs-grid-row">
        <div id="ik2rdi" class="gjs-grid-column">
            <h2 id="i83vu9" class="gjs-heading">Your verification code is <br />
                <span id="i7owfh" class="gjs-text-blue">{{ $verificationCode }}</span>
            </h2>
        </div>
    </div>
</body>

</html>