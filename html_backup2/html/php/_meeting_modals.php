<!-- 상세보기 모달 -->
<div id="details-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <img id="modal-details-img" src="" alt="모임 이미지" class="modal-img">
        <div class="modal-header">
            <h2 id="modal-details-title"></h2>
            <div>
                <span id="modal-details-category" class="card-category"></span>
                <span id="modal-details-status" class="card-status"></span>
            </div>
        </div>
        <div class="modal-body">
            <p id="modal-details-description"></p>
            <div class="modal-details-info">
                <span>🗓️ 날짜: <strong id="modal-details-datetime"></strong></span>
                <span>📍 장소: <strong id="modal-details-location"></strong></span>
                <span>👥 인원: <strong id="modal-details-members"></strong></span>
                <span>👤 개설자: <strong id="modal-details-organizer"></strong></span>
            </div>
            <div class="modal-details-participants">
                <h4>참여자 목록</h4>
                <ul id="modal-details-participants-list">
                    <!-- 참여자 닉네임이 여기에 동적으로 추가됩니다. -->
                </ul>
            </div>
        </div>
        <div class="modal-footer" id="modal-details-footer">
            <!-- 버튼이 동적으로 여기에 추가됩니다. -->
        </div>
    </div>
</div>

<!-- 새 모임 만들기 모달 -->
<div id="create-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2>새 모임 만들기</h2>
        <form id="create-meeting-form" action="create_meeting.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="create-title">제목</label>
                <input type="text" id="create-title" name="title" placeholder="예: 주말 아침 함께 테니스 칠 분!" required>
            </div>
            <div class="form-group">
                <label for="create-image">대표 사진</label>
                <input type="file" id="create-image" name="meeting_image" accept="image/*">
            </div>
            <div class="form-group">
                <label for="create-category">카테고리</label>
                <select id="create-category" name="category" required>
                    <option value="취미 및 여가">취미 및 여가</option>
                    <option value="운동 및 액티비티">운동 및 액티비티</option>
                    <option value="성장 및 배움">성장 및 배움</option>
                    <option value="문화 및 예술">문화 및 예술</option>
                    <option value="푸드 및 드링크">푸드 및 드링크</option>
                    <option value="여행 및 탐방">여행 및 탐방</option>
                    <option value="봉사 및 참여">봉사 및 참여</option>
                </select>
            </div>
            <div class="form-group">
                <label for="create-description">상세 설명</label>
                <textarea id="create-description" name="description" rows="4" placeholder="모임에 대한 상세한 설명을 적어주세요." required></textarea>
            </div>
            <div class="form-group">
                <label for="create-location">장소</label>
                <input type="text" id="create-location" name="location" placeholder="예: 아산시 방축동 실내테니스장" required>
            </div>
            <div class="form-group form-row">
                <div class="form-group-half">
                    <label for="create-date">모임 날짜</label>
                    <input type="date" id="create-date" name="meeting_date" required>
                </div>
                <div class="form-group-half">
                    <label for="create-time">모임 시간</label>
                    <input type="time" id="create-time" name="meeting_time" required>
                </div>
            </div>
            <div class="form-group">
                <label for="create-max-members">최대 인원</label>
                <input type="number" id="create-max-members" name="max_members" min="2" placeholder="2명 이상" required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary">생성하기</button>
            </div>
        </form>
    </div>
</div>

<!-- 추천 모달 -->
<div id="recommendation-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2>이런 모임은 어떠세요?</h2>
        <p>입력하신 내용과 비슷한 모임이 이미 있어요.</p>
        <div id="recommendation-list" class="recommendation-list">
            <!-- 추천 모임이 여기에 동적으로 추가됩니다. -->
        </div>
        <div class="modal-footer recommendation-footer">
            <button id="force-create-meeting-btn" class="btn-primary">그냥 새로 만들게요</button>
        </div>
    </div>
</div>
