const showUI = (message, imgUrl, isError = false) => {
  return `
  <div style="display:flex; flex-direction: column; justify-content: center; align-items: center; height: 100%">
    <div style="text-align: center; padding: 20px; ">
      <img src="${imgUrl}" style="width: 50px; height: 50px; margin-bottom: 10px"/>
      <p style="color:${isError ? "#D32F2F" : "#6655ff"}; font-size:20px">${message}</p>
    </div>
  </div>
  `;
};

const ERROR_UI = showUI(
  "Không thể hiển thị link thanh toán",
  "https://img.payos.vn/static/img/payos_hara/failed.png",
  true
);
const SUCCESS_UI = showUI(
  "Đơn hàng đã thanh toán thành công",
  "https://img.payos.vn/static/img/payos_hara/success.png"
);

const isMobileScreen = () =>
  Boolean(
    navigator.userAgent.match(/Android/i) ||
      navigator.userAgent.match(/webOS/i) ||
      navigator.userAgent.match(/iPhone/i) ||
      navigator.userAgent.match(/iPad/i) ||
      navigator.userAgent.match(/iPod/i) ||
      navigator.userAgent.match(/BlackBerry/i) ||
      navigator.userAgent.match(/Windows Phone/i)
  );

const isJsonString = (str) => {
  if (!str) {
    return false;
  }
  try {
    JSON.parse(str);
    return true;
  } catch (e) {
    return false;
  }
};

document.addEventListener("DOMContentLoaded", async () => {
  const methodPayment = document.querySelector(".section-content-column").innerHTML;
  if (!methodPayment.toLowerCase().includes("vietqr")) return;

  const Haravan = window.Haravan.checkout;
  if (!Haravan) return;

  let paymentLinkOrigin = null;
  const orderId = Haravan.order_id;
  const financialStatus = Haravan.financial_status;
  // all flow UI in this div
  const contentImporter = document.createElement("div");
  contentImporter.style.border = "1px solid #d9d9d9 ";
  contentImporter.style.width = "100%";
  contentImporter.style.height = isMobileScreen() ? "620px" : "340px";
  const additionalContent = document.querySelector(".thank-you-additional-content");
  additionalContent.appendChild(contentImporter);

  // remove icon check
  document.querySelector(".section-header > .hanging-icon").remove();
  // change message default
  document.querySelector(".section-header > .os-header-heading > .os-description").innerHTML =
    "Cảm ơn vì đã mua hàng. Vui lòng thanh toán.";

  if (financialStatus === "paid") {
    contentImporter.innerHTML = SUCCESS_UI;
    return;
  }
  const handleFetchData = async (path, method = "get", body = null) => {
    const config = {
      method,
    };
    if (body) {
      config.body = JSON.stringify(body);
    }
    const response = await fetch(`${API_SERVER}/${path}`, config);
    if (!response.ok) {
      throw new Error(response.status);
    }
    const data = await response.json();
    return data;
  };

  const handlePostMessage = async (event) => {
    if (event.origin !== new URL(paymentLinkOrigin).origin) {
      return;
    }
    const eventData = isJsonString(event.data) ? JSON.parse(event.data) : undefined;
    if (!eventData) {
      return;
    }
    if (eventData.type !== "payment_response") return;
    const responseData = eventData.data;
    if (responseData?.status === "PAID") {
      contentImporter.innerHTML = SUCCESS_UI;
    }
  };

  try {
    const orderStatus = await handleFetchData(`get-status-order/${orderId}`);
    if (orderStatus === "paid") {
      contentImporter.innerHTML = SUCCESS_UI;
      return;
    }
    const paymentLink = await handleFetchData(`create-payment-link/${orderId}`, "POST", {
      redirect_uri: window.location.origin,
    });
    const { checkout_url } = paymentLink;
    paymentLinkOrigin = checkout_url;
    const paymentLinkDialogUrl = `${checkout_url}?iframe=true&redirect_uri=${window.location.origin}&embedded=true`;

    contentImporter.innerHTML = `
      <iframe src="${paymentLinkDialogUrl}" style="height: 100%; width: 100%; border: none"  allow="clipboard-read; clipboard-write"/>
    `;
    window.addEventListener("message", handlePostMessage);
  } catch (error) {
    contentImporter.innerHTML = ERROR_UI;
  }
});
