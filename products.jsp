<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<% 
String[][] products = {
    {"Áo Thun", "150000", "product1.jpg"},
    {"Quần Jeans", "250000", "product2.jpg"},
    {"Giày Sneaker", "350000", "product3.jpg"},
    {"Túi Xách", "200000", "product4.jpg"},
    {"Đồng Hồ", "300000", "product5.jpg"},
};
for (String[] product : products) {
%>
    <div class="product-card">
        <img src="images/<%= product[2] %>" alt="<%= product[0] %>">
        <h3><%= product[0] %></h3>
        <p><%= product[1] %> VNĐ</p>
        <button class="order-btn" data-id="<%= product[0] %>">Đặt Hàng</button>
    </div>
<% } %>